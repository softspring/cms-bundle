<?php

namespace Softspring\CmsBundle\Config\Model;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Block implements ConfigurationInterface
{
    protected string $blockName;

    public function __construct(string $blockName)
    {
        $this->blockName = $blockName;
    }

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('block');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->integerNode('revision')->isRequired()->end()

                ->scalarNode('render_template')->defaultValue("@block/{$this->blockName}/render.html.twig")->end()
                // ->scalarNode('edit_template')->end()
                // ->scalarNode('form_template')->end()

                ->booleanNode('esi')->defaultTrue()->end()
                ->integerNode('cache_ttl')->defaultFalse()->end()
                ->booleanNode('singleton')->defaultTrue()->end()
                ->booleanNode('static')->defaultFalse()->end()
                ->scalarNode('render_url')->end()

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
