<?php

namespace Softspring\CmsBundle\Test\Config\Model;

use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Config\Model\Block;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

class BlockTest extends TestCase
{
    public function testEmptyConfig()
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('The child config "revision" under "block" must be configured.');

        $processor = new Processor();
        $configuration = new Block('block_name');
        $config = $processor->processConfiguration($configuration, []);

        $this->assertIsArray($config);
    }

    public function testDefaultConfig()
    {
        $processor = new Processor();
        $configuration = new Block('block_name');
        $config = $processor->processConfiguration($configuration, [
            'block' => [
                'revision' => 1,
            ],
        ]);

        $this->assertEquals([
            'revision' => 1,
            'render_template' => '@block/block_name/render.html.twig',
            'esi' => true,
            'cache_ttl' => false,
            'singleton' => true,
            'static' => false,
            'form_options' => [],
            'form_fields' => [],
        ], $config);
    }

    public function testCustomConfig()
    {
        $processor = new Processor();
        $configuration = new Block('block_name');
        $config = $processor->processConfiguration($configuration, [
            'block' => [
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
            'static' => false,
            'form_options' => [],
            'form_fields' => [],
        ], $config);
    }

    public function testInvalidStaticAndNotSingleton()
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Invalid configuration for path "block": A block defined as static must be singleton.');

        $processor = new Processor();
        $configuration = new Block('block_name');
        $processor->processConfiguration($configuration, [
            'block' => [
                'revision' => 1,
                'static' => true,
                'singleton' => false,
            ],
        ]);
    }

    public function testInvalidStaticAndFormFields()
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Invalid configuration for path "block": A block defined as static can not have form_fields.');

        $processor = new Processor();
        $configuration = new Block('block_name');
        $processor->processConfiguration($configuration, [
            'block' => [
                'revision' => 1,
                'static' => true,
                'singleton' => true,
                'form_fields' => [
                    'test' => [
                        'type' => 'text'
                    ],
                ],
            ],
        ]);
    }

    public function testInvalidStaticAndFormOptions()
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Invalid configuration for path "block": A block defined as static can not have form_options.');

        $processor = new Processor();
        $configuration = new Block('block_name');
        $processor->processConfiguration($configuration, [
            'block' => [
                'revision' => 1,
                'static' => true,
                'singleton' => true,
                'form_options' => [
                    'test' => [],
                ],
            ],
        ]);
    }

    public function testRenderUrl()
    {
        $processor = new Processor();
        $configuration = new Block('block_name');
        $config = $processor->processConfiguration($configuration, [
            'block' => [
                'revision' => 2,
                'render_url' => 'render_route',
            ],
        ]);

        $this->assertEquals([
            'revision' => 2,
            'render_template' => '@block/block_name/render.html.twig',
            'esi' => true,
            'cache_ttl' => false,
            'singleton' => true,
            'static' => false,
            'form_options' => [],
            'form_fields' => [],
            'render_url' => 'render_route',
        ], $config);
    }

    public function testCustomFormType()
    {
        $processor = new Processor();
        $configuration = new Block('block_name');
        $config = $processor->processConfiguration($configuration, [
            'block' => [
                'revision' => 2,
                'form_type' => 'App\\Form\\ExampleType',
            ],
        ]);

        $this->assertEquals([
            'revision' => 2,
            'render_template' => '@block/block_name/render.html.twig',
            'esi' => true,
            'cache_ttl' => false,
            'singleton' => true,
            'static' => false,
            'form_options' => [],
            'form_fields' => [],
            'form_type' => 'App\\Form\\ExampleType',
        ], $config);
    }
}
