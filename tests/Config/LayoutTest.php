<?php

namespace Softspring\CmsBundle\Test\Config;

use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Config\Model\Layout;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

class LayoutTest extends TestCase
{
    public function testEmptyConfig()
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('The child config "revision" under "layout" must be configured.');

        $processor = new Processor();
        $configuration = new Layout('layout_name');
        $config = $processor->processConfiguration($configuration, []);
    }

    public function testDefaultConfig()
    {
        $processor = new Processor();
        $configuration = new Layout('layout_name');
        $config = $processor->processConfiguration($configuration, [
            'layout' => [
                'revision' => 1,
            ],
        ]);

        $this->assertEquals(1, $config['revision']);
        $this->assertEquals('@cms/layout/layout_name/render.html.twig', $config['render_template']);
        $this->assertEquals('@cms/layout/layout_name/edit.html.twig', $config['edit_template']);
        $this->assertIsArray($config['containers']);
    }

    public function testCustomConfig()
    {
        $processor = new Processor();
        $configuration = new Layout('layout_name');
        $config = $processor->processConfiguration($configuration, [
            'layout' => [
                'revision' => 2,
                'render_template' => 'other_render_file.html.twig',
                'edit_template' => 'other_edit_file.html.twig',
                'containers' => [
                    'sidebar' => [],
                    'content' => [
                        'required' => true,
                        'allowed_modules' => ['html', 'image', 'landscape'],
                    ],
                ],
            ],
        ]);

        $this->assertEquals(2, $config['revision']);
        $this->assertEquals('other_render_file.html.twig', $config['render_template']);
        $this->assertEquals('other_edit_file.html.twig', $config['edit_template']);
        $this->assertIsArray($config['containers']);

        $this->assertArrayHasKey('sidebar', $config['containers']);
        $this->assertFalse($config['containers']['sidebar']['required']);
        $this->assertEquals([], $config['containers']['sidebar']['allowed_modules']);

        $this->assertArrayHasKey('content', $config['containers']);
        $this->assertTrue($config['containers']['content']['required']);
        $this->assertEquals(['html', 'image', 'landscape'], $config['containers']['content']['allowed_modules']);
    }
}
