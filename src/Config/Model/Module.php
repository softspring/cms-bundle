<?php

namespace Softspring\CmsBundle\Config\Model;

use Softspring\CmsBundle\Form\Module\DynamicFormModuleType;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Module implements ConfigurationInterface
{
    /**
     * @param iterable<ConfigExtensionInterface> $configExtensions
     */
    public function __construct(protected string $moduleName, protected iterable $configExtensions = [])
    {
    }

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('module');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->integerNode('revision')->isRequired()->end()
                ->booleanNode('enabled')->defaultTrue()->end()
                ->scalarNode('group')->defaultValue('default')->end()

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

        foreach ($this->configExtensions as $configExtension) {
            $configExtension->extend($rootNode);
        }

        return $treeBuilder;
    }
}
