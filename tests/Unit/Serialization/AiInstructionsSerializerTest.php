<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Test\Unit\Serialization;

use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Model\SiteInterface;
use Softspring\CmsBundle\Serialization\AiInstructionsSerializer;
use Softspring\CmsBundle\Serialization\MediaTypeRequirementsSerializer;
use Softspring\CmsBundle\Serialization\SensitiveValueSanitizer;
use Softspring\CmsBundle\Serialization\SiteSerializer;

class AiInstructionsSerializerTest extends TestCase
{
    public function testInstructionsRemainVisibleWithoutRawConfiguration(): void
    {
        $text = "Use this image for a hero.\nKeep space for readable text.";
        $site = $this->createStub(SiteInterface::class);
        $site->method('getConfig')->willReturn(['meta' => ['ai_instructions' => $text]]);
        $serializer = new SiteSerializer(new SensitiveValueSanitizer());
        self::assertSame($text, $serializer->summarize($site)['aiInstructions']);
        self::assertSame($text, $serializer->context($site)['aiInstructions']);
        self::assertArrayNotHasKey('config', $serializer->context($site));
        self::assertSame($text, new MediaTypeRequirementsSerializer()->describe('hero', ['meta' => ['ai_instructions' => $text]])['aiInstructions']);
        self::assertSame([], AiInstructionsSerializer::summarize([]));
        self::assertSame([], AiInstructionsSerializer::summarize(['meta' => ['ai_instructions' => ['arbitrary' => true]]]));
        self::assertSame([], AiInstructionsSerializer::summarize(['meta' => ['ai_instructions' => '  ']]));
    }
}
