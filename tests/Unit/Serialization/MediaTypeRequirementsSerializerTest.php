<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Test\Unit\Serialization;

use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Serialization\MediaTypeRequirementsSerializer;

class MediaTypeRequirementsSerializerTest extends TestCase
{
    public function testItDescribesGenericMediaTypeRequirements(): void
    {
        $serializer = new MediaTypeRequirementsSerializer();

        self::assertSame([
            'type' => 'hero',
            'label' => 'Hero image',
            'mediaType' => 'image',
            'isImage' => true,
            'uploadRequirements' => [
                'minWidth' => 1200,
                'mimeTypes' => ['image/jpeg'],
            ],
        ], $serializer->describe('hero', [
            'label' => 'Hero image',
            'type' => 'image',
            'upload_requirements' => [
                'minWidth' => 1200,
                'mimeTypes' => ['image/jpeg'],
            ],
        ]));
    }

    public function testItDoesNotAddGenerationMetadata(): void
    {
        $serializer = new MediaTypeRequirementsSerializer();

        $description = $serializer->describe('document', [
            'type' => 'file',
        ]);

        self::assertSame([
            'type' => 'document',
            'label' => 'document',
            'mediaType' => 'file',
            'isImage' => false,
            'uploadRequirements' => [],
        ], $description);
        self::assertArrayNotHasKey('generation', $description);
        self::assertArrayNotHasKey('promptRequirements', $description);
        self::assertArrayNotHasKey('modelSize', $description);
    }
}
