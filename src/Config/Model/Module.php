<?php

namespace Softspring\CmsBundle\Config\Model;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Module implements ConfigurationInterface
{
    protected string $moduleName;

    public function __construct(string $moduleName)
    {
        $this->moduleName = $moduleName;
    }

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('module');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->integerNode('revision')->isRequired()->end()

                ->scalarNode('render_template')->defaultValue("@module/{$this->moduleName}/render.html.twig")->end()
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
        ;

        return $treeBuilder;
    }
}