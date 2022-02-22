<?php

namespace Softspring\CmsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('sfs_cms');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('entity_manager')
                    ->defaultValue('default')
                ->end()

                ->arrayNode('site')
                    ->isRequired()
                    ->children()
                        ->enumNode('type')->values(['path', 'host'])->isRequired()->end()
                        ->scalarNode('class')->defaultValue('App\Entity\Site')->end()
                        ->scalarNode('route_param_name')->defaultValue('_site')->end()
                        ->scalarNode('find_field_name')->defaultValue('id')->end()
                    ->end()
                ->end()

                ->arrayNode('block')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('class')->defaultValue('App\Entity\Block')->end()
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

                ->arrayNode('layout')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('class')->defaultValue('App\Entity\Layout')->end()
                        ->scalarNode('find_field_name')->defaultValue('id')->end()
                    ->end()
                ->end()

                ->arrayNode('route')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('class')->defaultValue('App\Entity\Route')->end()
                        ->scalarNode('path_class')->defaultValue('App\Entity\RoutePath')->end()
                        ->scalarNode('find_field_name')->defaultValue('id')->end()
                    ->end()
                ->end()

                ->arrayNode('page')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('page_class')->defaultValue('App\Entity\Page')->end()
                        // ->scalarNode('page_path_class')->defaultValue('App\Entity\PagePath')->end()
                        ->scalarNode('find_field_name')->defaultValue('id')->end()
                    ->end()
                ->end()

                ->arrayNode('modules')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('abstract_class')->defaultValue('Softspring\CmsBundle\Model\AbstractModule')->end()
                        // ->scalarNode('page_path_class')->defaultValue('App\Entity\PagePath')->end()
//                        ->scalarNode('find_field_name')->defaultValue('id')->end()

                        ->arrayNode('types')
                            ->useAttributeAsKey('key')
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('entity')->isRequired()->end()
                                    ->scalarNode('form')->isRequired()->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('dynamic_modules')
                    ->useAttributeAsKey('key')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('render_template')->isRequired()->end()
                            ->scalarNode('edit_template')->end()
                            ->scalarNode('form_template')->end()

                            ->scalarNode('form_type')->end()

                            ->arrayNode('form_options')
                                ->useAttributeAsKey('key')
                                ->prototype('variable')->end()
                            ->end()

                            ->arrayNode('form_fields')
                                ->useAttributeAsKey('key')
                                ->arrayPrototype()
                                    ->children()
                                        ->scalarNode('type')->isRequired()->end()
                                        ->arrayNode('type_options')
                                            ->useAttributeAsKey('key')
                                            ->prototype('variable')->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()

            ->end()
        ;

        return $treeBuilder;
    }
}
