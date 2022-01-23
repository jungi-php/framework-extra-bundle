<?php

namespace Jungi\FrameworkExtraBundle;

use Jungi\FrameworkExtraBundle\DependencyInjection\Compiler\RegisterConvertersPass;
use Jungi\FrameworkExtraBundle\DependencyInjection\Compiler\RegisterMessageBodyMappersPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class JungiFrameworkExtraBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new RegisterMessageBodyMappersPass());
        $container->addCompilerPass(new RegisterConvertersPass());
    }
}
