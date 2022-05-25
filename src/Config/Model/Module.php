<?php

namespace Softspring\CmsBundle\Config\Model;

use Softspring\CmsBundle\Form\Module\DynamicFormModuleType;
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

                ->arrayNode('compatible_contents')
                    ->scalarPrototype()->end()
                ->end()

                ->scalarNode('module_type')->defaultValue(DynamicFormModuleType::class)->end()

                ->arrayNode('module_options')
                    ->useAttributeAsKey('key')
                    ->prototype('variable')->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
