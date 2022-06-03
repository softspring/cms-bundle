<?php

namespace Softspring\CmsBundle\Test\Config\Model;

use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Config\Model\Menu;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

class MenuTest extends TestCase
{
    public function testEmptyConfig()
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('The child config "revision" under "menu" must be configured.');

        $processor = new Processor();
        $configuration = new Menu('menu_name');
        $config = $processor->processConfiguration($configuration, []);

        $this->assertIsArray($config);
    }

    public function testDefaultConfig()
    {
        $processor = new Processor();
        $configuration = new Menu('menu_name');
        $config = $processor->processConfiguration($configuration, [
            'menu' => [
                'revision' => 1,
            ],
        ]);

        $this->assertEquals([
            'revision' => 1,
            'render_template' => '@menu/menu_name/render.html.twig',
            'esi' => true,
            'cache_ttl' => false,
            'singleton' => true,
        ], $config);
    }

    public function testCustomConfig()
    {
        $processor = new Processor();
        $configuration = new Menu('menu_name');
        $config = $processor->processConfiguration($configuration, [
            'menu' => [
                'revision' => 2,
                'render_template' => 'other_render_file.html.twig',
            ],
        ]);

        $this->assertEquals([
            'revision' => 2,
            'render_template' => 'other_render_file.html.twig',
            'esi' => true,
            'cache_ttl' => false,
            'singleton' => true,
        ], $config);
    }
}
