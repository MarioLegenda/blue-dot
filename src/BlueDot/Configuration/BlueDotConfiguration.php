<?php
/**
 * Created by PhpStorm.
 * User: pc
 * Date: 18.11.2016.
 * Time: 11:51
 */

namespace BlueDot\Configuration;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class BlueDotConfiguration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('configuration');

        $rootNode
            ->children()
                ->arrayNode('connection')
                    ->children()
                        ->scalarNode('host')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('database_name')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('user')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('password')->isRequired()->end()
                    ->end()
                ->end()
                ->arrayNode('simple')->isRequired()->cannotBeEmpty()
                    ->children()
                        ->arrayNode('select')->cannotBeEmpty()->useAttributeAsKey('name')
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('sql')->isRequired()->cannotBeEmpty()->end()
                                    ->arrayNode('parameters')->prototype('scalar')->end()
                                ->end()
                            ->end()
                        ->end()->end()
                        ->arrayNode('insert')->cannotBeEmpty()->useAttributeAsKey('name')
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('sql')->isRequired()->cannotBeEmpty()->end()
                                    ->arrayNode('parameters')->prototype('scalar')->end()
                                ->end()
                            ->end()
                        ->end()->end()
                        ->arrayNode('update')->cannotBeEmpty()->useAttributeAsKey('name')
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('sql')->isRequired()->cannotBeEmpty()->end()
                                    ->arrayNode('parameters')->prototype('scalar')->end()
                                ->end()
                            ->end()
                        ->end()->end()
                        ->arrayNode('delete')->cannotBeEmpty()->useAttributeAsKey('name')
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('sql')->isRequired()->cannotBeEmpty()->end()
                                    ->arrayNode('parameters')->prototype('scalar')->end()
                                ->end()
                            ->end()
                        ->end()->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}