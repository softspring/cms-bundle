<?php

namespace Softspring\CmsBundle\Test\Config;

use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Config\Model\Module;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

class ModuleTest extends TestCase
{
    public function testEmptyConfig()
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('The child config "revision" under "module" must be configured.');

        $processor = new Processor();
        $configuration = new Module('module_name');
        $config = $processor->processConfiguration($configuration, []);
    }

    public function testDefaultConfig()
    {
        $processor = new Processor();
        $configuration = new Module('module_name');
        $config = $processor->processConfiguration($configuration, [
            'module' => [
                'revision' => 1,
            ],
        ]);

        $this->assertEquals(1, $config['revision']);
        $this->assertEquals('@cms/module/module_name/render.html.twig', $config['render_template']);
        $this->assertIsArray($config['form_options']);
        $this->assertIsArray($config['form_fields']);
    }

    public function testCustomConfig()
    {
        $processor = new Processor();
        $configuration = new Module('module_name');
        $config = $processor->processConfiguration($configuration, [
            'module' => [
                'revision' => 2,
                'render_template' => 'other_render_file.html.twig',
            ]
        ]);

        $this->assertEquals(2, $config['revision']);
        $this->assertEquals('other_render_file.html.twig', $config['render_template']);
        $this->assertIsArray($config['form_options']);
        $this->assertIsArray($config['form_fields']);
    }
}