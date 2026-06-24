<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Test\Unit\DataCollector;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\DataCollector\ContentDataCollector;
use Softspring\CmsBundle\Entity\Site;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\Model\RouteInterface;
use Softspring\CmsBundle\Model\RoutePathInterface;
use Softspring\CmsBundle\Render\BlockRenderer;
use Softspring\CmsBundle\Render\ContentVersionRenderer;
use Softspring\CmsBundle\Render\MenuRenderer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Profiler\Profiler;
use Symfony\Contracts\Translation\TranslatorInterface;

class ContentDataCollectorTest extends TestCase
{
    public function testDoesNotCollectWhenProfilerIsDisabled(): void
    {
        $collector = $this->createCollector(profilerEnabled: false);

        $collector->collect(Request::create('/'), new Response());

        self::assertFalse($collector->isCmsRequest());
        self::assertSame([], $collector->getBlocks());
        self::assertSame([], $collector->getModules());
        self::assertSame([], $collector->getMenus());
        self::assertSame([], $collector->getCache());
        self::assertSame('', $collector->getSiteName());
        self::assertNull($collector->getSite());
        self::assertSame('', $collector->getLocale());
    }

    public function testDoesNotCollectNonCmsRequests(): void
    {
        $collector = $this->createCollector();
        $request = Request::create('/');
        $request->attributes->set('_controller', 'App\Controller\HomeController::index');

        $collector->collect($request, new Response());

        self::assertFalse($collector->isCmsRequest());
    }

    public function testCollectsCmsRequestDebugData(): void
    {
        $site = new Site();
        $site->setId('default');

        $content = $this->createContent($site);
        $publishedVersion = $this->createMock(ContentVersionInterface::class);
        $publishedVersion->method('getCreatedAt')->willReturn(new DateTime('2026-01-01 10:00:00'));
        $publishedVersion->method('getLayout')->willReturn('default');
        $content->method('getPublishedVersion')->willReturn($publishedVersion);

        $route = $this->createRoute($content);
        $routePath = $this->createRoutePath($route, '/en/home', 'en');
        $alternateRoutePath = $this->createRoutePath($route, '/es/home', 'es');
        $route->method('getPaths')->willReturn(new ArrayCollection([$routePath, $alternateRoutePath]));

        $request = Request::create('/en/home');
        $request->attributes->set('_controller', 'Softspring\CmsBundle\Controller\ContentController::renderRoutePath');
        $request->attributes->set('_sfs_cms_site', $site);
        $request->attributes->set('_sfs_cms_locale', 'en');
        $request->attributes->set('routePath', $routePath);

        $response = new Response();
        $response->headers->set('cache-control', 'max-age=60');
        $response->headers->set('last-modified', 'Wed, 01 Jan 2026 10:00:00 GMT');

        $collector = $this->createCollector();
        $collector->collect($request, $response);

        self::assertTrue($collector->isCmsRequest());
        self::assertSame('Translated default.name', $collector->getSiteName());
        self::assertSame($site, $collector->getSite());
        self::assertSame('en', $collector->getLocale());
        self::assertSame([['type' => 'block']], $collector->getBlocks());
        self::assertSame([['container' => 'main']], $collector->getModules());
        self::assertSame([['type' => 'menu']], $collector->getMenus());
        self::assertArrayHasKey('cache-control', $collector->getCache());
        self::assertArrayHasKey('last-modified', $collector->getCache());
        self::assertSame('Home', $collector->getTitle());
        self::assertFalse($collector->isEsiEnabled());
        self::assertFalse($collector->isFragmentsEnabled());
        self::assertFalse($collector->isHttpCacheEnabled());

        self::assertSame([
            'id' => 'route_home',
            'type' => RouteInterface::TYPE_CONTENT,
            'parent' => null,
            'content' => 'content-1',
            'symfonyRoute' => ['route' => 'app_home'],
            'redirectUrl' => null,
            'redirectType' => null,
            'currentPath' => [
                'path' => '/en/home',
                'locale' => 'en',
                'cacheTtl' => 60,
                'compiledPath' => '/compiled/en/home',
            ],
            'paths' => [
                ['path' => '/en/home', 'locale' => 'en', 'cacheTtl' => 60, 'compiledPath' => '/compiled/en/home'],
                ['path' => '/es/home', 'locale' => 'es', 'cacheTtl' => 60, 'compiledPath' => '/compiled/es/home'],
            ],
        ], $collector->getRoute());

        self::assertSame('content-1', $collector->getContent()['id']);
        self::assertSame(['en', 'es'], $collector->getContent()['locales']);
        self::assertSame('Home', $collector->getContent()['name']);
        self::assertSame('default', $collector->getContent()['publishedVersion']['layout']);
    }

    private function createCollector(bool $profilerEnabled = true): ContentDataCollector
    {
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->method('trans')->willReturnCallback(fn (string $id): string => 'Translated '.$id);

        $blockRenderer = $this->createMock(BlockRenderer::class);
        $blockRenderer->method('getDebugCollectorData')->willReturn([['type' => 'block']]);

        $menuRenderer = $this->createMock(MenuRenderer::class);
        $menuRenderer->method('getDebugCollectorData')->willReturn([['type' => 'menu']]);

        $contentRenderer = $this->createMock(ContentVersionRenderer::class);
        $contentRenderer->method('getDebugCollectorData')->willReturn([['container' => 'main']]);

        return new ContentDataCollector(
            $blockRenderer,
            $menuRenderer,
            $contentRenderer,
            'ttl',
            $translator,
            $profilerEnabled ? $this->createMock(Profiler::class) : null,
            null,
            null,
            null,
        );
    }

    /**
     * @return ContentInterface&MockObject
     */
    private function createContent(Site $site): ContentInterface
    {
        $content = $this->createMock(ContentInterface::class);
        $content->method('getId')->willReturn('content-1');
        $content->method('getLocales')->willReturn(['en', 'es']);
        $content->method('getDefaultLocale')->willReturn('en');
        $content->method('getSites')->willReturn(new ArrayCollection([$site]));
        $content->method('getIndexing')->willReturn(['index' => true]);
        $content->method('getName')->willReturn('Home');
        $content->method('getLastVersionNumber')->willReturn(7);
        $content->method('getStatus')->willReturn('published');
        $content->method('getExtraData')->willReturn(['foo' => 'bar']);

        return $content;
    }

    /**
     * @return RouteInterface&MockObject
     */
    private function createRoute(ContentInterface $content): RouteInterface
    {
        $route = $this->createMock(RouteInterface::class);
        $route->method('getId')->willReturn('route_home');
        $route->method('getType')->willReturn(RouteInterface::TYPE_CONTENT);
        $route->method('getParent')->willReturn(null);
        $route->method('getContent')->willReturn($content);
        $route->method('getSymfonyRoute')->willReturn(['route' => 'app_home']);
        $route->method('getRedirectUrl')->willReturn(null);
        $route->method('getRedirectType')->willReturn(null);

        return $route;
    }

    private function createRoutePath(RouteInterface $route, string $path, string $locale): RoutePathInterface
    {
        $routePath = $this->createMock(RoutePathInterface::class);
        $routePath->method('getRoute')->willReturn($route);
        $routePath->method('getPath')->willReturn($path);
        $routePath->method('getLocale')->willReturn($locale);
        $routePath->method('getCacheTtl')->willReturn(60);
        $routePath->method('getCompiledPath')->willReturn('/compiled'.$path);

        return $routePath;
    }
}
