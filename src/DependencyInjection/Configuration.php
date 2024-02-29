<?php

namespace Softspring\CmsBundle\DependencyInjection;

use Softspring\CmsBundle\Entity;
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

                ->booleanNode('admin')->defaultTrue()->end()

                ->arrayNode('collections')
                    ->scalarPrototype()->end()
                ->end()

                ->arrayNode('site')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->enumNode('identification')->values(['domain', 'path'])->defaultValue('domain')->end()
                        ->scalarNode('class')->defaultValue(Entity\Site::class)->end()
                        ->booleanNode('throw_not_found')->defaultTrue()->end()
                    ->end()
                ->end()

                ->arrayNode('block')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('class')->defaultValue(Entity\Block::class)->end()
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
                        ->scalarNode('class')->defaultValue(Entity\Route::class)->end()
                        ->scalarNode('path_class')->defaultValue(Entity\RoutePath::class)->end()
                        ->scalarNode('find_field_name')->defaultValue('id')->end()
                    ->end()
                ->end()

                ->arrayNode('content')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('content_class')->defaultValue(Entity\Content::class)->end()
                        ->scalarNode('content_version_class')->defaultValue(Entity\ContentVersion::class)->end()
                        ->scalarNode('page_class')->defaultValue(Entity\Page::class)->end()
                        ->scalarNode('find_field_name')->defaultValue('id')->end()
                        ->booleanNode('save_compiled')->defaultTrue()->end()
                        ->scalarNode('prefix_compiled')->defaultValue('')->end()
                        ->booleanNode('cache_last_modified')->defaultFalse()->end()
                    ->end()
                ->end()

                ->arrayNode('menu')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('class')->defaultValue(Entity\Menu::class)->end()
                        ->scalarNode('item_class')->defaultValue(Entity\MenuItem::class)->end()
                        ->scalarNode('find_field_name')->defaultValue('id')->end()
                    ->end()
                ->end()

            ->end()
        ;

        return $treeBuilder;
    }
}
