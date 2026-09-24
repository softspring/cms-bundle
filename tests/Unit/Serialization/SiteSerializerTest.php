<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Test\Unit\Serialization;

use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Model\SiteInterface;
use Softspring\CmsBundle\Serialization\SensitiveValueSanitizer;
use Softspring\CmsBundle\Serialization\SiteSerializer;

class SiteSerializerTest extends TestCase
{
    public function testItSummarizesSiteConfiguration(): void
    {
        $serializer = new SiteSerializer(new SensitiveValueSanitizer());
        $site = $this->site();

        self::assertSame([
            'id' => 'main',
            'canonical' => [
                'scheme' => 'https',
                'host' => 'example.com',
                'port' => 443,
            ],
            'locales' => ['en', 'es'],
            'defaultLocale' => 'en',
            'allowedContentTypes' => ['page'],
            'hosts' => ['example.com'],
            'metadataKeys' => ['brand', 'api_key'],
        ], $serializer->summarize($site));
    }

    public function testItIncludesSanitizedRawConfigAndMetadataWhenRequested(): void
    {
        $serializer = new SiteSerializer(new SensitiveValueSanitizer());

        $summary = $serializer->summarize($this->site(), true);

        self::assertSame('[redacted]', $summary['config']['private_key']);
        self::assertSame('[redacted]', $summary['metadata']['api_key']);
    }

    public function testItReturnsFullContextWithoutAiSpecificAliases(): void
    {
        $serializer = new SiteSerializer(new SensitiveValueSanitizer());

        $context = $serializer->context($this->site(), true);

        self::assertSame('main', $context['id']);
        self::assertSame(['brand' => 'Softspring', 'api_key' => 'metadata-secret'], $context['metadata']);
        self::assertSame(['page'], $context['config']['allowed_content_types']);
        self::assertArrayNotHasKey('aiInstructions', $context);
    }

    private function site(): SiteInterface
    {
        $site = $this->createMock(SiteInterface::class);
        $site->method('getId')->willReturn('main');
        $site->method('getCanonicalScheme')->willReturn('https');
        $site->method('getCanonicalHost')->willReturn('example.com');
        $site->method('getCanonicalPort')->willReturn(443);
        $site->method('getConfig')->willReturn([
            'locales' => ['en', 'es'],
            'default_locale' => 'en',
            'allowed_content_types' => ['page'],
            'hosts' => ['example.com'],
            'private_key' => 'config-secret',
        ]);
        $site->method('getMetadata')->willReturn([
            'brand' => 'Softspring',
            'api_key' => 'metadata-secret',
        ]);

        return $site;
    }
}
