<?php

namespace Softspring\CmsBundle\Test\Unit\Config\Router;

use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Entity\Route;
use Softspring\CmsBundle\Entity\RoutePath;
use Softspring\CmsBundle\Entity\Site;
use Softspring\CmsBundle\Manager\RouteManagerInterface;
use Softspring\CmsBundle\Routing\UrlGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class UrlGeneratorTest extends TestCase
{
    protected RouteManagerInterface&MockObject $routeManager;
    protected CmsConfig&MockObject $cmsConfig;
    protected LoggerInterface&MockObject $logger;

    protected function setUp(): void
    {
        $this->routeManager = $this->createMock(RouteManagerInterface::class);
        $this->cmsConfig = $this->createMock(CmsConfig::class);
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    public function testGetUrlUsesCanonicalHostForDomainOnlySiteWithoutCurrentRequest(): void
    {
        $site = $this->createSite([
            'hosts' => [
                ['domain' => 'example.org', 'locale' => false, 'scheme' => 'https', 'canonical' => true],
            ],
            'paths' => [],
            'default_locale' => 'es',
        ]);
        $route = $this->createRoute($site, 'home', [
            ['locale' => 'es', 'path' => 'inicio'],
        ]);

        $this->assertSame('https://example.org/inicio', $this->createUrlGenerator()->getUrl($route, 'es', $site));
    }

    public function testGetPathUsesSiteLocalePathWithoutCurrentRequest(): void
    {
        $site = $this->createSite([
            'hosts' => [
                ['domain' => 'example.org', 'locale' => false, 'scheme' => 'https', 'canonical' => true],
            ],
            'paths' => [
                ['path' => '/es', 'locale' => 'es'],
                ['path' => '/en', 'locale' => 'en'],
            ],
            'default_locale' => 'es',
        ]);
        $route = $this->createRoute($site, 'home', [
            ['locale' => 'es', 'path' => 'inicio'],
        ]);

        $this->assertSame('/es/inicio', $this->createUrlGenerator()->getPath($route, null, $site));
    }

    public function testGetUrlUsesCurrentRequestHostForPathOnlySite(): void
    {
        $site = $this->createSite([
            'hosts' => [],
            'paths' => [
                ['path' => '/es', 'locale' => 'es'],
            ],
            'default_locale' => 'es',
        ]);
        $route = $this->createRoute($site, 'home', [
            ['locale' => 'es', 'path' => 'inicio'],
        ]);
        $request = Request::create('https://softspring.local/');

        $this->assertSame('https://softspring.local/es/inicio', $this->createUrlGenerator($request)->getUrl($route, 'es', $site));
    }

    public function testGetUrlFailsClearlyForPathOnlySiteWithoutCurrentRequest(): void
    {
        $site = $this->createSite([
            'hosts' => [],
            'paths' => [
                ['path' => '/es', 'locale' => 'es'],
            ],
            'default_locale' => 'es',
        ]);
        $route = $this->createRoute($site, 'home', [
            ['locale' => 'es', 'path' => 'inicio'],
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Can not generate an absolute URL without a site host or a current request');

        $this->createUrlGenerator()->getUrl($route, 'es', $site);
    }

    public function testGetPathUsesFirstRouteSiteWhenProvidedSiteDoesNotOwnRoute(): void
    {
        $routeSite = $this->createSite([
            'hosts' => [],
            'paths' => [
                ['path' => '/es/blog', 'locale' => 'es'],
            ],
            'default_locale' => 'es',
        ], 'blog');
        $wrongSite = $this->createSite([
            'hosts' => [],
            'paths' => [
                ['path' => '/es', 'locale' => 'es'],
            ],
            'default_locale' => 'es',
        ]);
        $route = $this->createRoute($routeSite, 'post', [
            ['locale' => 'es', 'path' => 'entrada'],
        ]);

        $this->assertSame('/es/blog/entrada', $this->createUrlGenerator()->getPath($route, 'es', $wrongSite));
    }

    protected function createUrlGenerator(?Request $request = null): UrlGenerator
    {
        $requestStack = new RequestStack();
        if ($request instanceof Request) {
            $requestStack->push($request);
        }

        return new UrlGenerator($requestStack, $this->routeManager, $this->cmsConfig, [], $this->logger);
    }

    protected function createSite(array $config, string $id = 'default'): Site
    {
        $site = new Site();
        $site->setId($id);
        $site->setConfig($config);

        return $site;
    }

    protected function createRoute(Site $site, string $id, array $paths): Route
    {
        $route = new Route();
        $route->setId($id);
        $route->addSite($site);

        foreach ($paths as $pathConfig) {
            $path = new RoutePath();
            $path->setLocale($pathConfig['locale']);
            $path->setPath($pathConfig['path']);
            $route->addPath($path);
        }

        return $route;
    }
}
