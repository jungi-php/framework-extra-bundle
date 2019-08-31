<?php

namespace Jungi\FrameworkExtraBundle\DependencyInjection;

use Jungi\FrameworkExtraBundle\Http\ResponseFactory;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
final class JungiFrameworkExtraExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../../resources/config'));
        $loader->load('services.xml');

        if (class_exists(SerializerInterface::class)) {
            $loader->load('serializer_mappers.xml');
        }

        $responseFactory = $container->getDefinition(ResponseFactory::class);
        $responseFactory->replaceArgument(0, $config['entity_response']['default_content_type']);
    }
}
