<?php

namespace Jungi\FrameworkExtraBundle\DependencyInjection\Compiler;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use Jungi\FrameworkExtraBundle\Annotation\AnnotationInterface;
use Jungi\FrameworkExtraBundle\Annotation\ArgumentInterface;
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
        $aliases = array();

        foreach ($container->getAliases() as $alias => $id) {
            if ($id->isPublic() && !$id->isPrivate()) {
                $aliases[(string) $id][] = $alias;
            }
        }

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

            foreach ($classRefl->getMethods(\ReflectionMethod::IS_PUBLIC) as $methodRefl) {
                if ($methodRefl->isAbstract()
                    || $methodRefl->isConstructor()
                    || $methodRefl->isDestructor()
                    || 'setContainer' === $methodRefl->name
                ) {
                    continue;
                }

                $methodAnnotations = array();
                $argumentAnnotations = array();
                $existingParameters = array_map(function ($parameter) {
                    return $parameter->name;
                }, $methodRefl->getParameters());

                foreach ($this->annotationReader->getMethodAnnotations($methodRefl) as $annotation) {
                    $annotationClass = get_class($annotation);

                    if ($annotation instanceof ArgumentInterface) {
                        if (!in_array($annotation->getArgumentName(), $existingParameters, true)) {
                            throw new InvalidArgumentException(sprintf(
                                'Expected to have the argument "%s" in "%s::%s()", but it\'s not present.',
                                $annotation->getArgumentName(),
                                $methodRefl->class,
                                $methodRefl->name
                            ));
                        }

                        if (isset($argumentAnnotations[$annotation->getArgumentName()][$annotationClass])) {
                            throw new InvalidArgumentException(sprintf(
                                'Annotation "%s" occurred more than once for the argument "%s" at "%s::%s()".',
                                $annotationClass,
                                $annotation->getArgumentName(),
                                $methodRefl->class,
                                $methodRefl->name
                            ));
                        }

                        $argumentAnnotations[$annotation->getArgumentName()][$annotationClass] = $annotation;
                    } elseif ($annotation instanceof AnnotationInterface) {
                        if (isset($methodAnnotations[$annotationClass])) {
                            throw new InvalidArgumentException(sprintf(
                                'Annotation "%s" occurred more than once at "%s::%s()".',
                                $annotationClass,
                                $methodRefl->class,
                                $methodRefl->name
                            ));
                        }

                        $methodAnnotations[$annotationClass] = $annotation;
                    }
                }

                $suffixId = '__invoke' === $methodRefl->name ? '' : '::'. $methodRefl->name;

                if ($methodAnnotations) {
                    $refMap[$id . $suffixId] = $this->registerAnnotationLocator($container, $id . $suffixId, array_values($methodAnnotations));
                }

                foreach ($argumentAnnotations as $argumentName => $annotations) {
                    $entryId = $id . $suffixId . '$' . $argumentName;
                    $refMap[$entryId] = $this->registerAnnotationLocator($container, $entryId, array_values($annotations));
                }

                foreach ($aliases[$id] ?? [] as $alias) {
                    if ($methodAnnotations) {
                        $refMap[$alias . $suffixId] = clone $refMap[$id . $suffixId];
                    }

                    foreach ($argumentAnnotations as $argumentName => $annotations) {
                        $refMap[$alias . $suffixId . '$' . $argumentName] = clone $refMap[$id . $suffixId . '$' . $argumentName];
                    }
                }
            }
        }

        $refId = ServiceLocatorTagPass::register($container, $refMap);
        $container->setAlias('jungi.controller_annotation_locator', (string) $refId);
    }

    private function registerAnnotationLocator(ContainerBuilder $container, string $id, array $objects): Reference
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
}