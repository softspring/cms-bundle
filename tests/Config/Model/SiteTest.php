<?php

namespace Softspring\CmsBundle\Test\Config\Model;

use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Config\Model\Site;
use Symfony\Component\Config\Definition\Processor;

class SiteTest extends TestCase
{
    public function testEmptyDefaultConfiguration()
    {
        $processor = new Processor();
        $configuration = new Site('site_name');
        $config = $processor->processConfiguration($configuration, []);

        $this->assertEquals([
            'allowed_content_types' => ['page'],
            'locales' => ['es'],
            'default_locale' => 'es',
            'https_redirect' => true,
            'extra' => [],
            'hosts' => [],
            'paths' => [],
            'slash_route' => [
                'enabled' => false,
            ],
            'error_pages' => [],
        ], $config);
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
            ],
        ]);

        $this->assertEquals([
            'allowed_content_types' => ['page', 'post'],
            'locales' => ['es', 'en'],
            'default_locale' => 'en',
            'https_redirect' => false,
            'extra' => [],
            'hosts' => [],
            'paths' => [],
            'slash_route' => [
                'enabled' => false,
            ],
            'error_pages' => [],
        ], $config);
    }
}
