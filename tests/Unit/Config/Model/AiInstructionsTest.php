<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Test\Unit\Config\Model;

use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Config\Model\Block;
use Softspring\CmsBundle\Config\Model\Content;
use Softspring\CmsBundle\Config\Model\Layout;
use Softspring\CmsBundle\Config\Model\Menu;
use Softspring\CmsBundle\Config\Model\MetadataExtension;
use Softspring\CmsBundle\Config\Model\Module;
use Softspring\CmsBundle\Config\Model\Site;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

class AiInstructionsTest extends TestCase
{
    public function testGenericMetadataCarriesMultilineInstructionsForAllCmsDefinitions(): void
    {
        $cases = [[new Module('example', [new MetadataExtension()]), ['revision' => 1]], [new Content('example', [new MetadataExtension()]), ['revision' => 1, 'entity_class' => 'Example']],
            [new Layout('example', [new MetadataExtension()]), ['revision' => 1]], [new Menu('example', [new MetadataExtension()]), ['revision' => 1]],
            [new Block('example', [new MetadataExtension()]), ['revision' => 1]], [new Site('example', [new MetadataExtension()]), ['enabled' => false]]];
        foreach ($cases as [$configuration, $minimum]) {
            $processor = new Processor();
            $defaults = $processor->processConfiguration($configuration, [$minimum]);
            self::assertArrayNotHasKey('ai_instructions', $defaults);
            $configured = $processor->processConfiguration($configuration, [$minimum + ['meta' => ['ai_instructions' => "Purpose.\nUsage guidance.", 'custom' => ['arbitrary' => true]]]]);
            self::assertSame("Purpose.\nUsage guidance.", $configured['meta']['ai_instructions']);
            self::assertTrue($configured['meta']['custom']['arbitrary']);
            try {
                $processor->processConfiguration($configuration, [$minimum + ['ai_instructions' => ['invalid']]]);
                self::fail('Instructions belong inside metadata, not at the definition root.');
            } catch (InvalidConfigurationException $exception) {
                self::assertStringContainsString('ai_instructions', $exception->getMessage());
            }
        }
    }

    public function testContentAndLayoutContainersAcceptIndependentInstructions(): void
    {
        foreach ([[new Content('example', [new MetadataExtension()]), ['revision' => 1, 'entity_class' => 'Example']], [new Layout('example', [new MetadataExtension()]), ['revision' => 1]]] as [$configuration, $minimum]) {
            $config = new Processor()->processConfiguration($configuration, [$minimum + ['containers' => ['main' => []], 'meta' => ['containers' => ['main' => ['ai_instructions' => 'Compose the main body here.']]]]]);
            self::assertSame('Compose the main body here.', $config['meta']['containers']['main']['ai_instructions']);
        }
    }
}
