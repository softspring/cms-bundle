<?php

namespace Softspring\CmsBundle\Test\Unit\Config\Model;

use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Config\Model\Module;
use Softspring\CmsBundle\Form\Module\DynamicFormModuleType;
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

        $this->assertIsArray($config);
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

        $this->assertEquals([
            'revision' => 1,
            'enabled' => true,
            'render_template' => '@module/module_name/render.html.twig',
            'module_type' => DynamicFormModuleType::class,
            'module_options' => [],
            'compatible_contents' => [],
            'group' => 'default',
        ], $config);
    }

    public function testCustomConfig()
    {
        $processor = new Processor();
        $configuration = new Module('module_name');
        $config = $processor->processConfiguration($configuration, [
            'module' => [
                'revision' => 2,
                'render_template' => 'other_render_file.html.twig',
            ],
        ]);

        $this->assertEquals([
            'revision' => 2,
            'enabled' => true,
            'render_template' => 'other_render_file.html.twig',
            'module_type' => DynamicFormModuleType::class,
            'module_options' => [],
            'compatible_contents' => [],
            'group' => 'default',
        ], $config);
    }
}
