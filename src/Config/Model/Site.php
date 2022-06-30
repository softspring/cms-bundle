<?php

namespace Softspring\CmsBundle\Config\Model;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Site implements ConfigurationInterface
{
    protected string $siteName;

    public function __construct(string $siteName)
    {
        $this->siteName = $siteName;
    }

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('site');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->validate()
                ->ifTrue(fn ($config) => empty($config['hosts']) && empty($config['paths']))
                ->thenInvalid('Invalid configuration, either hosts either paths must be set for a valid site')
            ->end()
            ->children()
                ->arrayNode('allowed_content_types')
                    ->defaultValue(['page'])
                    ->scalarPrototype()->end()
                ->end()

                ->arrayNode('locales')
                    ->defaultValue(['es'])
                    ->scalarPrototype()->end()
                ->end()

                ->scalarNode('default_locale')->defaultValue('es')->end()

                ->booleanNode('https_redirect')->defaultTrue()->end()
                ->booleanNode('locale_path_redirect_if_empty')->defaultTrue()->end()

                ->arrayNode('extra')
                    ->useAttributeAsKey('key')
                    ->prototype('variable')->end()
                ->end()

                ->arrayNode('hosts')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('domain')->isRequired()->end()
                            ->scalarNode('locale')->defaultFalse()->end()
                            ->enumNode('scheme')->defaultValue('https')->values(['http', 'https', false])->end()
                            ->booleanNode('canonical')->defaultFalse()->end()
                            ->booleanNode('redirect_to_canonical')->defaultFalse()->end()
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('paths')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('path')->isRequired()->end()
                            ->scalarNode('locale')->defaultFalse()->end()
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('slash_route')
                    ->validate()
                        ->ifTrue(fn ($config) => $config['enabled'] && empty($config['behaviour']))
                        ->thenInvalid('If slash_route option is enabled, it requires a behaviour')
                    ->end()
                    ->validate()
                        ->ifTrue(fn ($config) => $config['enabled'] && ($config['behaviour'] ?? '') == 'redirect_to_route_with_user_language' && empty($config['route']))
                        ->thenInvalid('redirect_to_route_with_user_language behaviour requires a route')
                    ->end()
                    ->validate()
                        ->ifTrue(fn ($config) => $config['enabled'] && ($config['behaviour'] ?? '') == 'redirect_to_route_with_user_language' && empty($config['redirect_code']))
                        ->thenInvalid('redirect_to_route_with_user_language behaviour requires a redirect_code')
                    ->end()
                    ->canBeEnabled()
                    ->children()
                        ->enumNode('behaviour')->values(['redirect_to_route_with_user_language'])->end()
                        ->scalarNode('route')->end()
                        ->integerNode('redirect_code')->end()
                    ->end()
                ->end()

                // TODO NOT YET IMPLEMENTED
                ->arrayNode('error_pages')
                    ->useAttributeAsKey('key')
                    ->prototype('variable')->end()
                ->end()

            ->end()
        ;

        return $treeBuilder;
    }
}
