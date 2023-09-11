<?php

namespace Softspring\CmsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('sfs_cms');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('entity_manager')
                    ->defaultValue('default')
                ->end()

                ->arrayNode('collections')
                    ->scalarPrototype()->end()
                ->end()

                ->arrayNode('site')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->enumNode('identification')->values(['domain', 'path'])->defaultValue('domain')->end()
                        ->scalarNode('class')->defaultValue('Softspring\CmsBundle\Entity\Site')->end()
                        ->booleanNode('throw_not_found')->defaultTrue()->end()
                    ->end()
                ->end()

                ->arrayNode('block')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('class')->defaultValue('Softspring\CmsBundle\Entity\Block')->end()
                        ->scalarNode('find_field_name')->defaultValue('id')->end()

                        ->arrayNode('types')
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('name')
                                        ->defaultNull()
                                    ->end()
                                    ->scalarNode('description')
                                        ->defaultNull()
                                    ->end()
                                    ->scalarNode('admin_form')->isRequired()->end()
                                    ->scalarNode('render_template')->isRequired()->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('route')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('class')->defaultValue('Softspring\CmsBundle\Entity\Route')->end()
                        ->scalarNode('path_class')->defaultValue('Softspring\CmsBundle\Entity\RoutePath')->end()
                        ->scalarNode('find_field_name')->defaultValue('id')->end()
                    ->end()
                ->end()

                ->arrayNode('content')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('content_class')->defaultValue('Softspring\CmsBundle\Entity\Content')->end()
                        ->scalarNode('content_version_class')->defaultValue('Softspring\CmsBundle\Entity\ContentVersion')->end()
                        ->scalarNode('find_field_name')->defaultValue('id')->end()
                        ->booleanNode('save_compiled')->defaultTrue()->end()
                        ->scalarNode('prefix_compiled')->defaultValue('')->end()
                    ->end()
                ->end()

                ->arrayNode('menu')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('class')->defaultValue('Softspring\CmsBundle\Entity\Menu')->end()
                        ->scalarNode('item_class')->defaultValue('Softspring\CmsBundle\Entity\MenuItem')->end()
                        ->scalarNode('find_field_name')->defaultValue('id')->end()
                    ->end()
                ->end()

            ->end()
        ;

        return $treeBuilder;
    }
}
