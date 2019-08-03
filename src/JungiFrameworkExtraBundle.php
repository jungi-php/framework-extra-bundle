<?php

namespace Jungi\FrameworkExtraBundle;

use Jungi\FrameworkExtraBundle\DependencyInjection\Compiler\RegisterConversionMappersPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class JungiFrameworkExtraBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new RegisterConversionMappersPass());
    }
}
