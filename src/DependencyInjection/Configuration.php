<?php

namespace Jungi\FrameworkExtraBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $builder = new TreeBuilder('jungi_framework_extra');
        $rootNode = $builder->getRootNode();

        $rootNode
            ->children()
                ->booleanNode('serializer')->defaultTrue()->end()
                ->scalarNode('default_content_type')->defaultValue('application/json')->end()
            ->end();

        return $builder;
    }
}
