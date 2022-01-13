<?php

namespace Jungi\FrameworkExtraBundle;

use Jungi\FrameworkExtraBundle\DependencyInjection\Compiler\RegisterConvertersPass;
use Jungi\FrameworkExtraBundle\DependencyInjection\Compiler\RegisterMessageBodyMappersPass;
use Symfony\Component\Config\Resource\ClassExistenceResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\Serializer\DependencyInjection\SerializerPass;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class JungiFrameworkExtraBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new RegisterMessageBodyMappersPass());
        $container->addCompilerPass(new RegisterConvertersPass());

        $container->addResource(new ClassExistenceResource(SerializerPass::class));
        if (class_exists(SerializerPass::class)) {
            $container->addCompilerPass(new SerializerPass('jungi.serializer'));
        }
    }
}
