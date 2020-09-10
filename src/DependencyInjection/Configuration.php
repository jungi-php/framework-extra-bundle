<?php

namespace Jungi\FrameworkExtraBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder('jungi_framework_extra');
        $rootNode = $builder->getRootNode();

        $rootNode
            ->children()
                ->booleanNode('serializer')->defaultTrue()->end()
                ->arrayNode('request')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('default_content_type')->defaultValue('application/json')->end()
                    ->end()
                ->end()
                ->arrayNode('entity_response')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('default_content_type')->defaultValue('application/json')->end()
                    ->end()
                ->end()
            ->end();

        return $builder;
    }
}
