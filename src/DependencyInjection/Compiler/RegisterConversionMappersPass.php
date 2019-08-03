<?php

namespace Jungi\FrameworkExtraBundle\DependencyInjection\Compiler;

use Jungi\FrameworkExtraBundle\Http\Conversion\MessageBodyConversionManager;
use Jungi\FrameworkExtraBundle\Http\MediaTypeUtils;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
final class RegisterConversionMappersPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $mapperMap = [];
        foreach ($container->findTaggedServiceIds('jungi.message_conversion_mapper') as $id => $mediaTypes) {
            foreach ($mediaTypes as $mediaType) {
                if (!isset($mediaType['media_type'])) {
                    throw new InvalidArgumentException(sprintf(
                        'Service "%s" must define the "media_type" attribute on "jungi.message_conversion_mapper" tag.'.
                        $id
                    ));
                }
                if (!MediaTypeUtils::isSpecific($mediaType['media_type'])) {
                    throw new InvalidArgumentException(sprintf(
                        'Service "%s" has the invalid media type "%s", it should be specific eg. "application/json".'.
                        $id,
                        $mediaType['media_type']
                    ));
                }

                $mapperMap[$mediaType['media_type']] = new Reference($id);
            }
        }

        $messageBodyConversionManager = $container->getDefinition(MessageBodyConversionManager::class);
        $messageBodyConversionManager->setArgument(0, ServiceLocatorTagPass::register($container, $mapperMap));
    }
}
