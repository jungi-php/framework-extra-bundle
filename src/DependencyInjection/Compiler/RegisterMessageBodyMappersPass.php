<?php

namespace Jungi\FrameworkExtraBundle\DependencyInjection\Compiler;

use Jungi\FrameworkExtraBundle\Http\MediaTypeUtils;
use Jungi\FrameworkExtraBundle\Http\MessageBodyMapperManager;
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
final class RegisterMessageBodyMappersPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $map = [];
        foreach ($container->findTaggedServiceIds('jungi.message_body_mapper') as $id => $attributes) {
            foreach ($attributes as $attribute) {
                if (!isset($attribute['media_type'])) {
                    throw new InvalidArgumentException(sprintf('Service "%s" must define the "media_type" attribute on "jungi.message_conversion_mapper" tag.', $id));
                }
                if (!MediaTypeUtils::isSpecific($attribute['media_type'])) {
                    throw new InvalidArgumentException(sprintf('Service "%s" has the invalid media type "%s", it should be specific eg. "application/json".', $id, $attribute['media_type']));
                }

                $map[$attribute['media_type']] = new Reference($id);
            }
        }

        $definition = $container->getDefinition(MessageBodyMapperManager::class);
        $definition->setArgument(0, ServiceLocatorTagPass::register($container, $map));
    }
}
