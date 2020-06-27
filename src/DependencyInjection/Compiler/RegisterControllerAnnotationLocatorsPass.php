<?php

namespace Jungi\FrameworkExtraBundle\DependencyInjection\Compiler;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use Jungi\FrameworkExtraBundle\Annotation\AnnotationInterface;
use Jungi\FrameworkExtraBundle\Annotation\ArgumentAnnotationInterface;
use Jungi\FrameworkExtraBundle\DependencyInjection\ExportableObject;
use Jungi\FrameworkExtraBundle\DependencyInjection\SimpleContainer;
use Jungi\FrameworkExtraBundle\DependencyInjection\StatefulObject;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
final class RegisterControllerAnnotationLocatorsPass implements CompilerPassInterface
{
    private $controllerTag;
    private $annotationReader;

    public function __construct(string $controllerTag = 'controller.service_arguments', Reader $annotationReader = null)
    {
        $this->controllerTag = $controllerTag;
        $this->annotationReader = $annotationReader ?: new AnnotationReader();
    }

    public function process(ContainerBuilder $container)
    {
        $refMap = array();

        foreach ($container->findTaggedServiceIds($this->controllerTag) as $id => $tags) {
            $definition = $container->getDefinition($id);
            $class = $definition->getClass();

            // resolve the class
            if (null === $class) {
                while ($definition instanceof ChildDefinition) {
                    $definition = $container->findDefinition($definition->getParent());
                    $class = $definition->getClass();
                }
            }

            $classRefl = $container->getReflectionClass($class);
            if (!$classRefl) {
                throw new InvalidArgumentException(sprintf('Class "%s" used for service "%s" cannot be found.', $class, $id));
            }

            $annotations = array_filter($this->annotationReader->getClassAnnotations($classRefl), function ($annotation) {
                return $annotation instanceof AnnotationInterface;
            });
            $refMap[$id] = $this->registerContainer($container, $id, $annotations);

            foreach ($classRefl->getMethods(\ReflectionMethod::IS_PUBLIC) as $methodRefl) {
                if ($methodRefl->isAbstract() || $methodRefl->isConstructor() || $methodRefl->isDestructor() || 'setContainer' === $methodRefl->name) {
                    continue;
                }

                $methodAnnotations = [];
                $argumentAnnotations = [];
                $existingParameters = [];

                foreach ($methodRefl->getParameters() as $parameter) {
                    $existingParameters[] = $parameter->name;
                }

                foreach ($this->annotationReader->getMethodAnnotations($methodRefl) as $annotation) {
                    if ($annotation instanceof ArgumentAnnotationInterface) {
                        if (!in_array($annotation->getArgumentName(), $existingParameters, true)) {
                            throw new \InvalidArgumentException(sprintf(
                                'Expected to have the argument "%s" in "%s::%s()", but it\'s not present.',
                                $annotation->getArgumentName(),
                                $classRefl->getName(),
                                $methodRefl->getName()
                            ));
                        }

                        if (!isset($argumentAnnotations[$annotation->getArgumentName()])) {
                            $argumentAnnotations[$annotation->getArgumentName()] = [];
                        }

                        $argumentAnnotations[$annotation->getArgumentName()][] = $annotation;
                    } elseif ($annotation instanceof AnnotationInterface) {
                        $methodAnnotations[] = $annotation;
                    }
                }

                $entryId = $id.'::'.$methodRefl->name;
                $refMap[$entryId] = $this->registerContainer($container, $entryId, $annotations);

                foreach ($argumentAnnotations as $argumentName => $annotations) {
                    $entryId = $id.'::'.$methodRefl->name.'$'.$argumentName;
                    $refMap[$entryId] = $this->registerContainer($container, $entryId, $annotations);
                }
            }
        }

        $refId = ServiceLocatorTagPass::register($container, $refMap);
        $container->setAlias('jungi.controller_annotation_locator', (string) $refId);
    }

    private function registerContainer(ContainerBuilder $container, string $id, array $objects): Reference
    {
        foreach ($objects as $object) {
            if (!$object instanceof ExportableObject) {
                throw new \InvalidArgumentException('Only objects marked as exportable can be dumped to service container.');
            }
        }

        $childDefinitions = [];
        foreach ($objects as $object) {
            $childDefinitions[get_class($object)] = $this->dumpObject($object);
        }

        $definition = (new Definition(SimpleContainer::class))
            ->setPublic(false)
            ->addArgument($childDefinitions);

        $definitionId = 'jungi.controller_annotations.'.ContainerBuilder::hash($id);
        $container->setDefinition($definitionId, $definition);

        return new Reference($definitionId);
    }

    private function dumpObject(ExportableObject $object): Definition
    {
        $definition = new Definition(get_class($object));

        if (!$object instanceof StatefulObject) {
            return $definition;
        }

        $properties = array();
        $refl = new \ReflectionClass($object);

        do {
            foreach ($refl->getProperties() as $property) {
                $property->setAccessible(true);

                $value = $property->getValue($object);
                if (is_object($value)) {
                    if (!$value instanceof ExportableObject) {
                        throw new \InvalidArgumentException('Unable to dump an object that contains non exportable objects.');
                    }

                    $value = $this->dumpObject($object);
                }

                if (is_resource($value)) {
                    throw new \InvalidArgumentException('Unable to dump an object that contains resources.');
                }

                $properties[$property->getName()] = $value;
            }
        } while ($refl = $refl->getParentClass());

        $definition
            ->setFactory(get_class($object) . '::fromState')
            ->addArgument($properties);

        return $definition;
    }
}
