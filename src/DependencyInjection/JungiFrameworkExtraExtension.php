<?php

namespace Jungi\FrameworkExtraBundle\DependencyInjection;

use Jungi\FrameworkExtraBundle\Controller\ArgumentResolver\RequestBodyValueResolver;
use Jungi\FrameworkExtraBundle\EventListener\EntityResponseListener;
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
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../../config'));
        $loader->load('services.xml');
        $loader->load('attributes.xml');

        if ($config['serializer'] && interface_exists(SerializerInterface::class)) {
            $loader->load('serializer.xml');
        }

        $entityResponseListener = $container->getDefinition(EntityResponseListener::class);
        $entityResponseListener->replaceArgument(1, $config['default_content_type']);

        $requestBodyValueResolver = $container->getDefinition(RequestBodyValueResolver::class);
        $requestBodyValueResolver->replaceArgument(2, $config['default_content_type']);
    }
}
