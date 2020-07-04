<?php

namespace Jungi\FrameworkExtraBundle\DependencyInjection\Compiler;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use Jungi\FrameworkExtraBundle\Annotation\AnnotationInterface;
use Jungi\FrameworkExtraBundle\Annotation\ArgumentAnnotationInterface;
use Jungi\FrameworkExtraBundle\DependencyInjection\Exporter\DefaultObjectExporter;
use Jungi\FrameworkExtraBundle\DependencyInjection\Exporter\ObjectExporterInterface;
use Jungi\FrameworkExtraBundle\DependencyInjection\SimpleContainer;
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
    private $objectExporter;

    public function __construct(string $controllerTag = 'controller.service_arguments', Reader $annotationReader = null, ObjectExporterInterface $objectExporter = null)
    {
        $this->controllerTag = $controllerTag;
        $this->annotationReader = $annotationReader ?: new AnnotationReader();
        $this->objectExporter = $objectExporter ?: new DefaultObjectExporter();
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

            $class = $container->getParameterBag()->resolveValue($class);
            $classRefl = $container->getReflectionClass($class);

            if (!$classRefl) {
                throw new InvalidArgumentException(sprintf('Class "%s" used for service "%s" cannot be found.', $class, $id));
            }

            $annotations = array_filter($this->annotationReader->getClassAnnotations($classRefl), function ($annotation) {
                return $annotation instanceof AnnotationInterface;
            });
            if ($annotations) {
                $this->assertAnnotationsAreUnique($annotations, sprintf('class "%s"', $class));
                $refMap[$id] = $this->registerContainer($container, $id, $annotations);
            }

            foreach ($classRefl->getMethods(\ReflectionMethod::IS_PUBLIC) as $methodRefl) {
                if ($methodRefl->isAbstract()
                    || $methodRefl->isConstructor()
                    || $methodRefl->isDestructor()
                    || 'setContainer' === $methodRefl->name
                ) {
                    continue;
                }

                $methodAnnotations = [];
                $argumentAnnotations = [];
                $existingParameters = array_map(function ($parameter) {
                    return $parameter->name;
                }, $methodRefl->getParameters());

                foreach ($this->annotationReader->getMethodAnnotations($methodRefl) as $annotation) {
                    if ($annotation instanceof ArgumentAnnotationInterface) {
                        if (!in_array($annotation->getArgumentName(), $existingParameters, true)) {
                            throw new InvalidArgumentException(sprintf(
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

                if ($methodAnnotations) {
                    $this->assertAnnotationsAreUnique($methodAnnotations, sprintf('method "%s::%s()"', $class, $methodRefl->name));

                    $entryId = $id . '::' . $methodRefl->name;
                    $refMap[$entryId] = $this->registerContainer($container, $entryId, $methodAnnotations);
                }

                foreach ($argumentAnnotations as $argumentName => $annotations) {
                    if (!$annotations) {
                        continue;
                    }

                    $this->assertAnnotationsAreUnique(
                        $annotations,
                        sprintf('argument "%s::%s($%s)"', $class, $methodRefl->name, $argumentName)
                    );

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
        $exportedObjects = [];
        foreach ($objects as $object) {
            $exportedObjects[get_class($object)] = $this->objectExporter->export($object);
        }

        $definition = (new Definition(SimpleContainer::class))
            ->setPublic(false)
            ->addArgument($exportedObjects);

        $definitionId = 'jungi.controller_annotations.'.ContainerBuilder::hash($id);
        $container->setDefinition($definitionId, $definition);

        return new Reference($definitionId);
    }

    private function assertAnnotationsAreUnique(array $annotations, string $where): void
    {
        $classes = [];
        foreach ($annotations as $annotation) {
            $class = get_class($annotation);
            if (in_array($class, $classes, true)) {
                throw new InvalidArgumentException(sprintf(
                    'Annotation "%s" occurred more than once at %s.',
                    $class,
                    $where
                ));
            }

            $classes[] = $class;
        }
    }
}
