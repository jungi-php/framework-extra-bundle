<?php

namespace Jungi\FrameworkExtraBundle\DependencyInjection\Compiler;

use Jungi\FrameworkExtraBundle\Converter\ConverterManager;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 *
 * @internal
 */
final class RegisterConvertersPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $map = [];
        foreach ($container->findTaggedServiceIds('jungi.converter') as $id => $attributes) {
            foreach ($attributes as $attribute) {
                if (!isset($attribute['type'])) {
                    throw new InvalidArgumentException(sprintf('Service "%s" must define the "type" attribute on "jungi.converter" tag.', $id));
                }

                $map[$attribute['type']] = new Reference($id);
            }
        }

        $definition = $container->getDefinition(ConverterManager::class);
        $definition->setArgument(0, ServiceLocatorTagPass::register($container, $map));
    }
}
