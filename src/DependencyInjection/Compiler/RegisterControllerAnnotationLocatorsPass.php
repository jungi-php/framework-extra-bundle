<?php

namespace Jungi\FrameworkExtraBundle\DependencyInjection\Compiler;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use Jungi\FrameworkExtraBundle\Annotation\Annotation;
use Jungi\FrameworkExtraBundle\Annotation\Argument;
use Jungi\FrameworkExtraBundle\Annotation\RequestBody;
use Jungi\FrameworkExtraBundle\DependencyInjection\Exporter\DefaultObjectExporter;
use Jungi\FrameworkExtraBundle\DependencyInjection\Exporter\ObjectExporterInterface;
use Jungi\FrameworkExtraBundle\Utils\TypeUtils;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 *
 * @internal
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
        $refMap = [];
        $aliases = [];

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

                $methodAnnotations = [];
                $argumentAnnotations = [];
                $existingParameters = [];

                foreach ($methodRefl->getParameters() as $paramRefl) {
                    $existingParameters[$paramRefl->name] = $paramRefl;
                }

                foreach ($this->annotationReader->getMethodAnnotations($methodRefl) as $annotation) {
                    $annotationClass = get_class($annotation);

                    if ($annotation instanceof Argument) {
                        if (!isset($existingParameters[$annotation->argument()])) {
                            throw new InvalidArgumentException(sprintf('Expected to have the argument "%s" in "%s::%s()", but it\'s not present.', $annotation->argument(), $methodRefl->class, $methodRefl->name));
                        }
                        if (isset($argumentAnnotations[$annotation->argument()][$annotationClass])) {
                            throw new InvalidArgumentException(sprintf('Annotation "%s" occurred more than once for the argument "%s" at "%s::%s()".', $annotationClass, $annotation->argument(), $methodRefl->class, $methodRefl->name));
                        }

                        $argumentAnnotations[$annotation->argument()][$annotationClass] = $annotation;
                    } elseif ($annotation instanceof Annotation) {
                        if (isset($methodAnnotations[$annotationClass])) {
                            throw new InvalidArgumentException(sprintf('Annotation "%s" occurred more than once at "%s::%s()".', $annotationClass, $methodRefl->class, $methodRefl->name));
                        }

                        $methodAnnotations[$annotationClass] = $annotation;
                    }

                    if ($annotation instanceof RequestBody && null !== $annotation->type()) {
                        $paramRefl = $existingParameters[$annotation->argument()];

                        if (!$paramRefl->isArray()) {
                            throw new InvalidArgumentException(sprintf('Expected the argument "%s" to be of "%s" type, got "%s" in "%s::%s()".', $annotation->argument(), $annotation->type(), $paramRefl->getType()->getName(), $methodRefl->class, $methodRefl->name));
                        }
                        if (!TypeUtils::isCollection($annotation->type())) {
                            throw new InvalidArgumentException(sprintf('Expected the argument "%s" to be annotated as a collection type, got "%s" in "%s::%s()".', $annotation->argument(), $annotation->type(), $methodRefl->class, $methodRefl->name));
                        }
                    }
                }

                $suffixId = '__invoke' === $methodRefl->name ? '' : '::'.$methodRefl->name;

                if ($methodAnnotations) {
                    $refMap[$id.$suffixId] = RegisterControllerAttributeLocatorsPass::registerLocator($container, $this->objectExporter, $id.$suffixId, array_values($methodAnnotations));
                }

                foreach ($argumentAnnotations as $argumentName => $annotations) {
                    $entryId = $id.$suffixId.'$'.$argumentName;
                    $refMap[$entryId] = RegisterControllerAttributeLocatorsPass::registerLocator($container, $this->objectExporter, $entryId, array_values($annotations));
                }

                foreach ($aliases[$id] ?? [] as $alias) {
                    if ($methodAnnotations) {
                        $refMap[$alias.$suffixId] = clone $refMap[$id.$suffixId];
                    }

                    foreach ($argumentAnnotations as $argumentName => $annotations) {
                        $refMap[$alias.$suffixId.'$'.$argumentName] = clone $refMap[$id.$suffixId.'$'.$argumentName];
                    }
                }
            }
        }

        $refId = ServiceLocatorTagPass::register($container, $refMap);
        $container->setAlias(RegisterControllerAttributeLocatorsPass::SERVICE_ALIAS, (string) $refId);
    }
}
