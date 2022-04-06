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

//                ->arrayNode('site')
//                    ->isRequired()
//                    ->children()
//                        ->enumNode('type')->values(['path', 'host'])->isRequired()->end()
//                        ->scalarNode('class')->defaultValue('Softspring\CmsBundle\Entity\Site')->end()
//                        ->scalarNode('route_param_name')->defaultValue('_site')->end()
//                        ->scalarNode('find_field_name')->defaultValue('id')->end()
//                    ->end()
//                ->end()

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
                    ->end()
                ->end()

                ->arrayNode('menu')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('menu_class')->defaultValue('Softspring\CmsBundle\Entity\Menu')->end()
                        ->scalarNode('menu_item_class')->defaultValue('Softspring\CmsBundle\Entity\MenuItem')->end()
                        ->scalarNode('find_field_name')->defaultValue('id')->end()
                    ->end()
                ->end()

//                ->arrayNode('dynamic_modules')
//                    ->useAttributeAsKey('key')
//                    ->arrayPrototype()
//                        ->children()
//                            ->scalarNode('render_template')->isRequired()->end()
//                            ->scalarNode('edit_template')->end()
//                            ->scalarNode('form_template')->end()
//
//                            ->scalarNode('form_type')->end()
//
//                            ->arrayNode('form_options')
//                                ->useAttributeAsKey('key')
//                                ->prototype('variable')->end()
//                            ->end()
//
//                            ->arrayNode('form_fields')
//                                ->useAttributeAsKey('key')
//                                ->arrayPrototype()
//                                    ->children()
//                                        ->scalarNode('type')->isRequired()->end()
//                                        ->arrayNode('type_options')
//                                            ->useAttributeAsKey('key')
//                                            ->prototype('variable')->end()
//                                        ->end()
//                                    ->end()
//                                ->end()
//                            ->end()
//                        ->end()
//                    ->end()
//                ->end()

            ->end()
        ;

        return $treeBuilder;
    }
}
