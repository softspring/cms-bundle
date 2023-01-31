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
            ->validate()
                ->ifTrue(function ($config) {
                    return $config['static'] && !$config['singleton'];
                })
                ->thenInvalid('A block defined as static must be singleton.')
            ->end()
            ->validate()
                ->ifTrue(function ($config) {
                    return $config['static'] && !empty($config['form_fields']);
                })
                ->thenInvalid('A block defined as static can not have form_fields.')
            ->end()
            ->validate()
                ->ifTrue(function ($config) {
                    return $config['static'] && !empty($config['form_options']);
                })
                ->thenInvalid('A block defined as static can not have form_options.')
            ->end()
//            ->validate()
//                ->ifTrue(function ($config) {
//                    return $config['static'] && !empty($config['edit_template']);
//                })
//                ->thenInvalid('A block defined as static can not have edit_template.')
//            ->end()
//            ->validate()
//                ->ifTrue(function ($config) {
//                    return $config['static'] && !empty($config['form_template']);
//                })
//                ->thenInvalid('A block defined as static can not have form_template.')
//            ->end()
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

                // TODO review this ???
                ->scalarNode('form_type')->end()

                // TODO review this ???
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
