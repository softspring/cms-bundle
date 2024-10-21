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
            ->beforeNormalization()
                ->always()
                ->then(function ($configuration) {
                    $defaultCacheEnabled = $configuration['cache']['enabled'] ?? null;
                    $defaultCacheType = $configuration['cache']['type'] ?? 'none';

                    $contentCacheEnabled = $configuration['content']['cache']['enabled'] ?? $defaultCacheEnabled ?? null;
                    if (isset($contentCacheEnabled)) {
                        $configuration['content']['cache']['enabled'] = $contentCacheEnabled;
                        $configuration['content']['cache']['type'] = $configuration['content']['cache']['type'] ?? $defaultCacheType;
                    }

                    $menuCacheEnabled = $configuration['menu']['cache']['enabled'] ?? $defaultCacheEnabled ?? null;
                    if (isset($menuCacheEnabled)) {
                        $configuration['menu']['cache']['enabled'] = $menuCacheEnabled;
                        $configuration['menu']['cache']['type'] = $configuration['menu']['cache']['type'] ?? $defaultCacheType;
                        if ('ttl' !== $configuration['menu']['cache']['type']) {
                            $configuration['menu']['cache']['type'] = 'ttl'; // is the only supported now
                        }
                    }

                    $blockCacheEnabled = $configuration['block']['cache']['enabled'] ?? $defaultCacheEnabled ?? null;
                    if (isset($blockCacheEnabled)) {
                        $configuration['block']['cache']['enabled'] = $blockCacheEnabled;
                        $configuration['block']['cache']['type'] = $configuration['block']['cache']['type'] ?? $defaultCacheType;
                        if ('ttl' !== $configuration['block']['cache']['type']) {
                            $configuration['block']['cache']['type'] = 'ttl'; // is the only supported now
                        }
                    }

                    return $configuration;
                })
            ->end()

            ->children()
                ->scalarNode('entity_manager')
                    ->defaultValue('default')
                ->end()

                ->booleanNode('admin')->defaultTrue()->end()

                ->arrayNode('cache')
                    ->canBeDisabled()
                    ->children()
                        ->enumNode('type')->defaultValue(null)->values(['ttl', 'last_modified', 'none'])->end()
                    ->end()
                ->end()

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

                        ->arrayNode('cache')
                            ->children()
                                ->booleanNode('enabled')->end()
                                ->enumNode('type')->defaultNull()->values(['ttl', 'none'])->end()
                            ->end()
                        ->end()

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
                        /* @deprecated cache_last_modified since 5.3, will be removed in 6.0, use global sfs_cms.cache block */
                        ->booleanNode('cache_last_modified')->defaultFalse()->end()
                        ->arrayNode('cache')
                            ->children()
                                ->booleanNode('enabled')->end()
                                ->enumNode('type')->defaultNull()->values(['ttl', 'last_modified', 'none'])->end()
                            ->end()
                        ->end()
                        ->booleanNode('recompile')->defaultTrue()->end()
                    ->end()
                ->end()

                ->arrayNode('menu')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('class')->defaultValue(Menu::class)->end()
                        ->scalarNode('item_class')->defaultValue(MenuItem::class)->end()
                        ->scalarNode('find_field_name')->defaultValue('id')->end()
                        ->arrayNode('cache')
                            ->children()
                                ->booleanNode('enabled')->end()
                                ->enumNode('type')->defaultNull()->values(['ttl', 'none'])->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()

            ->end()
        ;

        return $treeBuilder;
    }
}
