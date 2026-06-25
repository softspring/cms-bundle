<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Test\Unit\Serialization;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\Model\RouteInterface;
use Softspring\CmsBundle\Model\RoutePathInterface;
use Softspring\CmsBundle\Model\SiteInterface;
use Softspring\CmsBundle\Serialization\PublishedContentSerializer;

#[AllowMockObjectsWithoutExpectations]
class PublishedContentSerializerTest extends TestCase
{
    public function testItSerializesPublishedContentSummaryWithFilteredRoutes(): void
    {
        $serializer = new PublishedContentSerializer();
        $content = $this->content();

        self::assertSame([
            'id' => 'content-1',
            'type' => 'page',
            'name' => 'Homepage',
            'defaultLocale' => 'en',
            'locales' => ['en', 'es'],
            'sites' => ['main'],
            'publishedVersion' => [
                'id' => 'version-1',
                'versionNumber' => 3,
                'layout' => 'default',
                'createdAt' => '2026-06-01T10:00:00+00:00',
            ],
            'routes' => [[
                'id' => 'route-1',
                'type' => RouteInterface::TYPE_CONTENT,
                'paths' => [[
                    'id' => 'path-en',
                    'locale' => 'en',
                    'path' => '/home',
                    'compiledPath' => '/home',
                    'cacheTtl' => 3600,
                    'sites' => ['main'],
                ]],
            ]],
        ], $serializer->summary($content, 'page', 'en'));
    }

    public function testItSerializesPublishedContentDetailWithOptionalData(): void
    {
        $serializer = new PublishedContentSerializer();
        $content = $this->content();
        $version = $content->getPublishedVersion();

        self::assertSame([
            'title' => ['en' => 'Homepage'],
        ], $serializer->detail($content, 'page', $version, null, true)['publishedVersion']['data']);
        self::assertSame([
            'title' => 'Homepage',
        ], $serializer->detail($content, 'page', $version, null, true)['publishedVersion']['seo']);
    }

    private function content(): ContentInterface
    {
        $site = $this->site('main');
        $version = $this->createMock(ContentVersionInterface::class);
        $version->method('getId')->willReturn('version-1');
        $version->method('getVersionNumber')->willReturn(3);
        $version->method('getLayout')->willReturn('default');
        $version->method('getCreatedAt')->willReturn(new DateTime('2026-06-01T10:00:00+00:00'));
        $version->method('getSeo')->willReturn(['title' => 'Homepage']);
        $version->method('getData')->willReturn(['title' => ['en' => 'Homepage']]);

        $content = $this->createMock(ContentInterface::class);
        $content->method('getId')->willReturn('content-1');
        $content->method('getName')->willReturn('Homepage');
        $content->method('getDefaultLocale')->willReturn('en');
        $content->method('getLocales')->willReturn(['en', 'es']);
        $content->method('getSites')->willReturn(new ArrayCollection([$site]));
        $content->method('getPublishedVersion')->willReturn($version);
        $content->method('getRoutes')->willReturn(new ArrayCollection([$this->route($site)]));
        $content->method('getExtraData')->willReturn(['featured' => true]);
        $content->method('getIndexing')->willReturn(['noindex' => false]);

        return $content;
    }

    private function route(SiteInterface $site): RouteInterface
    {
        $route = $this->createMock(RouteInterface::class);
        $route->method('getId')->willReturn('route-1');
        $route->method('getType')->willReturn(RouteInterface::TYPE_CONTENT);
        $route->method('getPaths')->willReturn(new ArrayCollection([
            $this->routePath('path-en', 'en', '/home', $site),
            $this->routePath('path-es', 'es', '/inicio', $site),
        ]));

        return $route;
    }

    private function routePath(string $id, string $locale, string $path, SiteInterface $site): RoutePathInterface
    {
        $routePath = $this->createMock(RoutePathInterface::class);
        $routePath->method('getId')->willReturn($id);
        $routePath->method('getLocale')->willReturn($locale);
        $routePath->method('getPath')->willReturn($path);
        $routePath->method('getCompiledPath')->willReturn($path);
        $routePath->method('getCacheTtl')->willReturn(3600);
        $routePath->method('getSites')->willReturn(new ArrayCollection([$site]));

        return $routePath;
    }

    private function site(string $id): SiteInterface
    {
        $site = $this->createMock(SiteInterface::class);
        $site->method('getId')->willReturn($id);

        return $site;
    }
}
