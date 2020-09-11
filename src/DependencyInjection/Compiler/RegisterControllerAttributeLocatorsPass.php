<?php

namespace Jungi\FrameworkExtraBundle\DependencyInjection\Compiler;

use Jungi\FrameworkExtraBundle\Attribute\RequestBody;
use Jungi\FrameworkExtraBundle\DependencyInjection\Exporter\DefaultObjectExporter;
use Jungi\FrameworkExtraBundle\DependencyInjection\Exporter\ObjectExporterInterface;
use Jungi\FrameworkExtraBundle\DependencyInjection\SimpleContainer;
use Jungi\FrameworkExtraBundle\Attribute\Attribute;
use Jungi\FrameworkExtraBundle\Utils\TypeUtils;
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
final class RegisterControllerAttributeLocatorsPass implements CompilerPassInterface
{
    public const SERVICE_ALIAS = 'jungi.controller_attribute_locator';

    private $controllerTag;
    private $objectExporter;

    public function __construct(string $controllerTag = 'controller.service_arguments', ObjectExporterInterface $objectExporter = null)
    {
        $this->controllerTag = $controllerTag;
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

                $suffixId = '__invoke' === $methodRefl->name ? '' : '::'.$methodRefl->name;

                if ($attributeRefls = $methodRefl->getAttributes(Attribute::class, \ReflectionAttribute::IS_INSTANCEOF)) {
                    $refMap[$id.$suffixId] = self::registerLocator($container, $this->objectExporter, $id.$suffixId, $this->invokeAttributes($attributeRefls));
                }

                foreach ($methodRefl->getParameters() as $paramRefl) {
                    if ($attributeRefls = $paramRefl->getAttributes(Attribute::class, \ReflectionAttribute::IS_INSTANCEOF)) {
                        $attributes = $this->invokeAttributes($attributeRefls);

                        foreach ($attributes as $attribute) {
                            if ($attribute instanceof RequestBody && null !== $attribute->type()) {
                                if ('array' !== $paramRefl->getType()->getName()) {
                                    throw new InvalidArgumentException(sprintf('Expected the argument "%s" to be of "%s" type, got "%s" in "%s::%s()".', $paramRefl->name, $attribute->type(), $paramRefl->getType()->getName(), $methodRefl->class, $methodRefl->name));
                                }
                                if (!TypeUtils::isCollection($attribute->type())) {
                                    throw new InvalidArgumentException(sprintf('Expected the argument "%s" to be annotated as a collection type, got "%s" in "%s::%s()".', $paramRefl->name, $attribute->type(), $methodRefl->class, $methodRefl->name));
                                }
                            }
                        }

                        $entryId = $id.$suffixId.'$'.$paramRefl->name;
                        $refMap[$entryId] = self::registerLocator($container, $this->objectExporter, $entryId, $attributes);
                    }
                }

                foreach ($aliases[$id] ?? [] as $alias) {
                    if (isset($refMap[$id.$suffixId])) {
                        $refMap[$alias.$suffixId] = clone $refMap[$id.$suffixId];
                    }

                    foreach ($methodRefl->getParameters() as $paramRefl) {
                        if (isset($refMap[$id . $suffixId . '$' . $paramRefl->name])) {
                            $refMap[$alias . $suffixId . '$' . $paramRefl->name] = clone $refMap[$id . $suffixId . '$' . $paramRefl->name];
                        }
                    }
                }
            }
        }

        $refId = ServiceLocatorTagPass::register($container, $refMap);
        $container->setAlias(self::SERVICE_ALIAS, (string) $refId);
    }

    /**
     * @param \ReflectionAttribute[]
     *
     * @return Attribute[]
     */
    private function invokeAttributes(array $attributeRefls): array
    {
        return array_map(function(\ReflectionAttribute $refl) {
            return $refl->newInstance();
        }, $attributeRefls);
    }

    /**
     * @param Attribute[] $attributes
     */
    public static function registerLocator(ContainerBuilder $container, ObjectExporterInterface $objectExporter, string $id, array $attributes): Reference
    {
        $exportedAttributes = [];
        foreach ($attributes as $attribute) {
            $exportedAttributes[get_class($attribute)] = $objectExporter->export($attribute);
        }

        $definition = (new Definition(SimpleContainer::class))
            ->setPublic(false)
            ->addArgument($exportedAttributes);

        $definitionId = 'jungi.controller_attributes.'.ContainerBuilder::hash($id);
        $container->setDefinition($definitionId, $definition);

        return new Reference($definitionId);
    }
}

