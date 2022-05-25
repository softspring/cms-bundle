<?php

namespace Softspring\CmsBundle\Config\Model;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Menu implements ConfigurationInterface
{
    protected string $menuName;

    public function __construct(string $menuName)
    {
        $this->menuName = $menuName;
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
            ->end()
        ;

        return $treeBuilder;
    }
}
