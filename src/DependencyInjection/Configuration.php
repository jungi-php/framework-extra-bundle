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
                ->scalarNode('default_content_type')->defaultValue('application/json')->end()
                ->arrayNode('entity_response')
                    ->setDeprecated('jungi/framework-extra-bundle', '1.1')
                    ->children()
                        ->scalarNode('default_content_type')
                            ->setDeprecated('jungi/framework-extra-bundle', '1.1', 'moved to the root node "jungi_framework_extra".')
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $builder;
    }
}
