<?php

namespace Softspring\CmsBundle\DependencyInjection;

use Softspring\CmsBundle\Entity\Block;
use Softspring\CmsBundle\Entity\Content;
use Softspring\CmsBundle\Entity\ContentVersion;
use Softspring\CmsBundle\Entity\Menu;
use Softspring\CmsBundle\Entity\MenuItem;
use Softspring\CmsBundle\Entity\Page;
use Softspring\CmsBundle\Entity\Route;
use Softspring\CmsBundle\Entity\RoutePath;
use Softspring\CmsBundle\Entity\Site;
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
                        ->scalarNode('class')->defaultValue(Site::class)->end()
                        ->booleanNode('throw_not_found')->defaultTrue()->end()
                    ->end()
                ->end()

                ->arrayNode('block')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('class')->defaultValue(Block::class)->end()
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
                        ->scalarNode('class')->defaultValue(Route::class)->end()
                        ->scalarNode('path_class')->defaultValue(RoutePath::class)->end()
                        ->scalarNode('find_field_name')->defaultValue('id')->end()
                    ->end()
                ->end()

                ->arrayNode('content')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('content_class')->defaultValue(Content::class)->end()
                        ->scalarNode('content_version_class')->defaultValue(ContentVersion::class)->end()
                        ->scalarNode('page_class')->defaultValue(Page::class)->end()
                        ->scalarNode('find_field_name')->defaultValue('id')->end()
                        ->booleanNode('save_compiled')->defaultTrue()->end()
                        ->scalarNode('prefix_compiled')->defaultValue('')->end()
                        ->booleanNode('cache_last_modified')->defaultFalse()->end()
                        ->booleanNode('recompile')->defaultTrue()->end()
                    ->end()
                ->end()

                ->arrayNode('menu')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('class')->defaultValue(Menu::class)->end()
                        ->scalarNode('item_class')->defaultValue(MenuItem::class)->end()
                        ->scalarNode('find_field_name')->defaultValue('id')->end()
                    ->end()
                ->end()

            ->end()
        ;

        return $treeBuilder;
    }
}
