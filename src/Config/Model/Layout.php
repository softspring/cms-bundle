<?php

namespace Softspring\CmsBundle\Config\Model;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Layout implements ConfigurationInterface
{
    protected string $layoutName;

    public function __construct(string $layoutName)
    {
        $this->layoutName = $layoutName;
    }

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('layout');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->integerNode('revision')->isRequired()->end()
                ->booleanNode('save_compiled')->defaultTrue()->end()

                ->scalarNode('render_template')->defaultValue("@layout/{$this->layoutName}/render.html.twig")->end()
                ->scalarNode('edit_template')->defaultValue("@layout/{$this->layoutName}/edit.html.twig")->end()

                ->arrayNode('compatible_contents')
                    ->scalarPrototype()->end()
                ->end()

                ->arrayNode('containers')
                    ->useAttributeAsKey('key')
                    ->arrayPrototype()
                    ->children()
                        ->booleanNode('required')->defaultFalse()->end()
                        ->arrayNode('allowed_modules')
                            ->scalarPrototype()->end()
                        ->end()
                    ->end()
                ->end()

            ->end()
        ;

        return $treeBuilder;
    }
}
