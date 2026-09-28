<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Test\Unit\Serialization;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Serialization\MediaImageSerializer;
use Softspring\CmsBundle\Serialization\MediaTypeRequirementsSerializer;
use Softspring\MediaBundle\Model\MediaInterface;
use Softspring\MediaBundle\Model\MediaVersionInterface;
use Softspring\MediaBundle\Type\MediaTypesCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MediaImageSerializerTest extends TestCase
{
    public function testItSerializesImageContextWithoutAiMetadataAlias(): void
    {
        $serializer = $this->serializer();
        $media = $this->media();

        $context = $serializer->context($media);

        self::assertSame('media-1', $context['id']);
        self::assertSame('hero', $context['type']);
        self::assertSame('https://admin.example.test/es/media/media-1', $context['adminUrl']);
        self::assertSame(['source' => 'editorial'], $context['metadata']);
        self::assertSame([
            'type' => 'hero',
            'label' => 'Hero',
            'mediaType' => 'image',
            'isImage' => true,
            'uploadRequirements' => ['minWidth' => 1200],
        ], $context['requirements']);
        self::assertSame('thumbnail', $context['thumbnail']['version']);
        self::assertSame('original', $context['versions'][0]['version']);
        self::assertArrayNotHasKey('aiMetadata', $context);
    }

    public function testItEscapesPreviewMarkdownTitle(): void
    {
        $serializer = $this->serializer();
        $media = $this->media('Hero [main] (desktop)');

        self::assertSame(
            "[![Hero \\[main\\] \\(desktop\\)](https://cdn.example.test/thumb.jpg)](https://admin.example.test/es/media/media-1)\n[Hero \\[main\\] \\(desktop\\)](https://admin.example.test/es/media/media-1)",
            $serializer->previewMarkdown($media),
        );
    }

    public function testItSerializesMissingThumbnail(): void
    {
        $serializer = $this->serializer();
        $media = $this->media(hasThumbnail: false);

        self::assertSame([
            'version' => '_thumbnail',
            'publicUrl' => null,
            'width' => null,
            'height' => null,
            'fileSize' => null,
            'mimeType' => null,
            'missing' => true,
            'url' => null,
            'uploadedAt' => null,
            'generatedAt' => null,
        ], $serializer->thumbnail($media, true));
    }

    private function serializer(): MediaImageSerializer
    {
        $mediaTypesCollection = $this->createMock(MediaTypesCollection::class);
        $mediaTypesCollection->method('getType')->with('hero')->willReturn([
            'label' => 'Hero',
            'type' => 'image',
            'upload_requirements' => ['minWidth' => 1200],
        ]);

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')->willReturnCallback(
            static fn (string $route, array $parameters, int $referenceType): string => sprintf(
                'https://admin.example.test/%s/media/%s',
                $parameters['_locale'],
                $parameters['media'],
            ),
        );

        $request = Request::create('/admin');
        $request->attributes->set('_locale', 'es');
        $requestStack = new RequestStack();
        $requestStack->push($request);

        return new MediaImageSerializer(
            $mediaTypesCollection,
            new MediaTypeRequirementsSerializer(),
            $urlGenerator,
            $requestStack,
        );
    }

    private function media(string $name = 'Hero image', bool $hasThumbnail = true): MediaInterface
    {
        $thumbnail = $this->version('thumbnail', 'https://cdn.example.test/thumb.jpg');
        $original = $this->version('original', 'https://cdn.example.test/original.jpg');

        $media = $this->createMock(MediaInterface::class);
        $media->method('getId')->willReturn('media-1');
        $media->method('getType')->willReturn('hero');
        $media->method('getPrivate')->willReturn(false);
        $media->method('getName')->willReturn($name);
        $media->method('getDescription')->willReturn('Hero description');
        $media->method('getAltTexts')->willReturn(null);
        $media->method('getMetadata')->willReturn(['source' => 'editorial']);
        $media->method('getVersion')->with('_thumbnail')->willReturn($hasThumbnail ? $thumbnail : null);
        $media->method('getVersions')->willReturn(new ArrayCollection([$original]));

        return $media;
    }

    private function version(string $versionName, string $publicUrl): MediaVersionInterface
    {
        $version = $this->createMock(MediaVersionInterface::class);
        $version->method('getVersion')->willReturn($versionName);
        $version->method('getPublicUrl')->willReturn($publicUrl);
        $version->method('getWidth')->willReturn(1200);
        $version->method('getHeight')->willReturn(800);
        $version->method('getFileSize')->willReturn(123456);
        $version->method('getFileMimeType')->willReturn('image/jpeg');
        $version->method('getUrl')->willReturn('/internal/'.$versionName.'.jpg');
        $version->method('getUploadedAt')->willReturn(new DateTime('2026-06-01T11:00:00+00:00'));
        $version->method('getGeneratedAt')->willReturn(new DateTime('2026-06-01T11:05:00+00:00'));

        return $version;
    }
}
