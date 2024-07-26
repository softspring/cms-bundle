<?php

namespace Softspring\CmsBundle\Config\Model;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Menu implements ConfigurationInterface
{
    /**
     * @param iterable<ConfigExtensionInterface> $configExtensions
     */
    public function __construct(protected string $menuName, protected iterable $configExtensions = [])
    {
    }

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('menu');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->integerNode('revision')->isRequired()->end()
                ->scalarNode('render_template')->defaultValue("@menu/{$this->menuName}/render.html.twig")->end()
                ->booleanNode('esi')->defaultTrue()->end()
                ->integerNode('cache_ttl')->defaultFalse()->end()
                ->booleanNode('singleton')->defaultTrue()->end()
                ->booleanNode('items')->defaultTrue()->end()

                ->scalarNode('form_template')->end()

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

        foreach ($this->configExtensions as $configExtension) {
            $configExtension->extend($rootNode);
        }

        return $treeBuilder;
    }
}
