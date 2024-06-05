<?php

namespace Softspring\CmsBundle\Config\Model;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Site implements ConfigurationInterface
{
    /**
     * @param iterable<ConfigExtensionInterface> $configExtensions
     */
    public function __construct(protected string $siteName, protected iterable $configExtensions = [])
    {
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
                    ->performNoDeepMerging()
                    ->defaultValue(['page'])
                    ->scalarPrototype()->end()
                ->end()

                ->arrayNode('locales')
                    ->performNoDeepMerging()
                    ->defaultValue(['es'])
                    ->scalarPrototype()->end()
                ->end()

                ->scalarNode('default_locale')->defaultValue('es')->end()

                ->booleanNode('https_redirect')->defaultTrue()->end()
                ->booleanNode('locale_path_redirect_if_empty')->defaultTrue()->end()

                ->arrayNode('extra')
                    ->performNoDeepMerging()
                    ->useAttributeAsKey('key')
                    ->prototype('variable')->end()
                ->end()

                ->arrayNode('hosts')
                    ->performNoDeepMerging()
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
                    ->performNoDeepMerging()
                    ->arrayPrototype()
                        ->beforeNormalization()
                            ->always(function ($config) {
                                $config['path'] = rtrim($config['path'], '/');

                                return $config;
                            })
                        ->end()
                        ->children()
                            ->scalarNode('path')->isRequired()->end()
                            ->scalarNode('locale')->defaultFalse()->end()
                            ->scalarNode('trailing_slash_on_root')->defaultFalse()->end()
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('slash_route')
                    ->performNoDeepMerging()
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
                        ->integerNode('redirect_code')->defaultValue(301)->end()
                    ->end()
                ->end()

                // TODO NOT YET IMPLEMENTED
                ->arrayNode('error_pages')
                    ->performNoDeepMerging()
                    ->useAttributeAsKey('key')
                    ->prototype('variable')->end()
                ->end()

                ->arrayNode('robots')
                    ->addDefaultsIfNotSet()
                    ->performNoDeepMerging()
                    ->children()
                        ->enumNode('mode')->defaultFalse()->values([false, 'static', 'dynamic'])->end()
                        ->scalarNode('static_file')->defaultValue('@site/default/robots.txt.twig')->end()
                    ->end()
                ->end()

                ->arrayNode('sitemaps')
                    ->performNoDeepMerging()
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('url')->defaultValue('sitemap.xml')->end()
                            ->scalarNode('default_priority')->defaultFalse()->end()
                            ->enumNode('default_changefreq')->defaultFalse()->values([false, 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never'])->end()
                            ->integerNode('cache_ttl')->defaultFalse()->end()
                            ->booleanNode('alternates')->setDeprecated('softspring/cms-bundle', '5.1', 'Use alternates_locales and alternates_sites')->defaultTrue()->end()
                            ->booleanNode('alternates_locales')->defaultTrue()->end()
                            ->booleanNode('alternates_sites')->defaultTrue()->end()
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('sitemaps_index')
                    ->canBeEnabled()
                    ->children()
                        ->scalarNode('url')->defaultFalse()->end()
                        ->integerNode('cache_ttl')->defaultFalse()->end()
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
