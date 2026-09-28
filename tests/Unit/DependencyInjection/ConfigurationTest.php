<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Test\Unit\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\DependencyInjection\Configuration;
use Softspring\CmsBundle\Entity\Block;
use Softspring\CmsBundle\Entity\Content;
use Softspring\CmsBundle\Entity\ContentVersion;
use Softspring\CmsBundle\Entity\Menu;
use Softspring\CmsBundle\Entity\MenuItem;
use Softspring\CmsBundle\Entity\Page;
use Softspring\CmsBundle\Entity\Route;
use Softspring\CmsBundle\Entity\RoutePath;
use Softspring\CmsBundle\Entity\Site;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends TestCase
{
    public function testDefaultConfiguration(): void
    {
        $config = $this->process([]);

        self::assertSame('default', $config['entity_manager']);
        self::assertTrue($config['admin']);
        self::assertSame(['enabled' => true, 'type' => null], $config['cache']);
        self::assertSame(['enabled' => true, 'response_cache_strategy' => 'default'], $config['esi']);
        self::assertSame([], $config['collections']);
        self::assertSame([
            'identification' => 'domain',
            'class' => Site::class,
            'throw_not_found' => true,
        ], $config['site']);
        self::assertSame([
            'class' => Block::class,
            'find_field_name' => 'id',
            'types' => [],
        ], $config['block']);
        self::assertSame([
            'class' => Route::class,
            'path_class' => RoutePath::class,
            'find_field_name' => 'id',
            'restricted_paths' => [],
        ], $config['route']);
        self::assertSame([
            'content_class' => Content::class,
            'content_version_class' => ContentVersion::class,
            'page_class' => Page::class,
            'find_field_name' => 'id',
            'save_compiled' => true,
            'autocompile_on_save' => false,
            'autocompile_on_publish' => true,
            'prefix_compiled' => '',
            'recompile' => true,
        ], $config['content']);
        self::assertSame([
            'class' => Menu::class,
            'item_class' => MenuItem::class,
            'find_field_name' => 'id',
        ], $config['menu']);
        self::assertSame(['expiration_ttl' => 2592000], $config['compiled']);
    }

    public function testGlobalCacheConfigurationIsPropagatedToContentMenuAndBlock(): void
    {
        $config = $this->process([
            'cache' => [
                'enabled' => true,
                'type' => 'last_modified',
            ],
        ]);

        self::assertSame(['enabled' => true, 'type' => 'last_modified'], $config['content']['cache']);
        self::assertSame(['enabled' => true, 'type' => 'ttl'], $config['menu']['cache']);
        self::assertSame(['enabled' => true, 'type' => 'ttl'], $config['block']['cache']);
    }

    public function testSpecificCacheConfigurationOverridesGlobalDefaults(): void
    {
        $config = $this->process([
            'cache' => [
                'enabled' => true,
                'type' => 'ttl',
            ],
            'content' => [
                'cache' => [
                    'enabled' => true,
                    'type' => 'last_modified',
                ],
            ],
            'menu' => [
                'cache' => [
                    'enabled' => true,
                    'type' => 'none',
                ],
            ],
            'block' => [
                'cache' => [
                    'enabled' => true,
                    'type' => 'none',
                ],
            ],
        ]);

        self::assertSame(['enabled' => true, 'type' => 'last_modified'], $config['content']['cache']);
        self::assertSame(['enabled' => true, 'type' => 'ttl'], $config['menu']['cache']);
        self::assertSame(['enabled' => true, 'type' => 'ttl'], $config['block']['cache']);
    }

    public function testLegacyContentCacheLastModifiedOptionIsNormalized(): void
    {
        $config = $this->process([
            'content' => [
                'cache_last_modified' => true,
            ],
        ]);

        self::assertSame(['type' => 'last_modified'], $config['content']['cache']);
        self::assertArrayNotHasKey('cache_last_modified', $config['content']);
    }

    public function testCustomConfigurationValuesAreKept(): void
    {
        $config = $this->process([
            'entity_manager' => 'cms',
            'admin' => false,
            'esi' => [
                'enabled' => false,
                'response_cache_strategy' => 'shortest_response',
            ],
            'collections' => ['cms', 'vendor/acme/cms'],
            'site' => [
                'identification' => 'path',
                'class' => 'App\Entity\Site',
                'throw_not_found' => false,
            ],
            'block' => [
                'class' => 'App\Entity\Block',
                'find_field_name' => 'code',
                'types' => [
                    'hero' => [
                        'name' => 'Hero',
                        'description' => 'Main hero block',
                        'admin_form' => 'App\Form\HeroType',
                        'render_template' => 'blocks/hero.html.twig',
                    ],
                ],
            ],
            'route' => [
                'class' => 'App\Entity\Route',
                'path_class' => 'App\Entity\RoutePath',
                'find_field_name' => 'slug',
                'restricted_paths' => ['admin'],
            ],
            'content' => [
                'content_class' => 'App\Entity\Content',
                'content_version_class' => 'App\Entity\ContentVersion',
                'page_class' => 'App\Entity\Page',
                'find_field_name' => 'slug',
                'save_compiled' => false,
                'autocompile_on_save' => true,
                'autocompile_on_publish' => false,
                'prefix_compiled' => 'cms_',
                'recompile' => false,
            ],
            'menu' => [
                'class' => 'App\Entity\Menu',
                'item_class' => 'App\Entity\MenuItem',
                'find_field_name' => 'code',
            ],
            'compiled' => [
                'expiration_ttl' => 60,
            ],
        ]);

        self::assertSame('cms', $config['entity_manager']);
        self::assertFalse($config['admin']);
        self::assertSame(['enabled' => false, 'response_cache_strategy' => 'shortest_response'], $config['esi']);
        self::assertSame(['cms', 'vendor/acme/cms'], $config['collections']);
        self::assertSame('App\Entity\Site', $config['site']['class']);
        self::assertSame('path', $config['site']['identification']);
        self::assertFalse($config['site']['throw_not_found']);
        self::assertSame('App\Entity\Block', $config['block']['class']);
        self::assertSame('code', $config['block']['find_field_name']);
        self::assertSame('Hero', $config['block']['types']['hero']['name']);
        self::assertSame('App\Entity\Route', $config['route']['class']);
        self::assertSame('App\Entity\RoutePath', $config['route']['path_class']);
        self::assertSame('slug', $config['route']['find_field_name']);
        self::assertSame(['admin'], $config['route']['restricted_paths']);
        self::assertSame('App\Entity\Content', $config['content']['content_class']);
        self::assertSame('App\Entity\ContentVersion', $config['content']['content_version_class']);
        self::assertSame('App\Entity\Page', $config['content']['page_class']);
        self::assertSame('slug', $config['content']['find_field_name']);
        self::assertFalse($config['content']['save_compiled']);
        self::assertTrue($config['content']['autocompile_on_save']);
        self::assertFalse($config['content']['autocompile_on_publish']);
        self::assertSame('cms_', $config['content']['prefix_compiled']);
        self::assertFalse($config['content']['recompile']);
        self::assertSame('App\Entity\Menu', $config['menu']['class']);
        self::assertSame('App\Entity\MenuItem', $config['menu']['item_class']);
        self::assertSame('code', $config['menu']['find_field_name']);
        self::assertSame(60, $config['compiled']['expiration_ttl']);
    }

    private function process(array $config): array
    {
        return (new Processor())->processConfiguration(new Configuration(), ['sfs_cms' => $config]);
    }
}
