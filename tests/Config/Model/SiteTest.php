<?php

namespace Softspring\CmsBundle\Test\Config\Model;

use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Config\Model\Site;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

class SiteTest extends TestCase
{
    public function testEmptyDefaultConfiguration()
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Invalid configuration for path "site": Invalid configuration, either hosts either paths must be set for a valid site');
        $processor = new Processor();
        $configuration = new Site('site_name');
        $config = $processor->processConfiguration($configuration, ['site' => []]);
    }

    public function testBasic()
    {
        $processor = new Processor();
        $configuration = new Site('site_name');
        $config = $processor->processConfiguration($configuration, ['site' => [
                'allowed_content_types' => ['page', 'post'],
                'locales' => ['es', 'en'],
                'default_locale' => 'en',
                'https_redirect' => false,
                'hosts' => [
                    ['domain' => 'example.org'],
                ],
            ],
        ]);

        $this->assertEquals([
            'allowed_content_types' => ['page', 'post'],
            'locales' => ['es', 'en'],
            'default_locale' => 'en',
            'https_redirect' => false,
            'locale_path_redirect_if_empty' => true,
            'extra' => [],
            'hosts' => [
                [
                    'domain' => 'example.org',
                    'locale' => false,
                    'scheme' => 'https',
                    'canonical' => false,
                    'redirect_to_canonical' => false,
                ],
            ],
            'paths' => [],
            'slash_route' => [
                'enabled' => false,
            ],
            'error_pages' => [],
            'sitemaps' => [],
            'sitemaps_index' => [ 'enabled' => false, 'url' => false ],
        ], $config);
    }
}
