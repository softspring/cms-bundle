<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Test\Unit\Helper;

use Closure;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Entity\Site;
use Softspring\CmsBundle\Helper\RoutingHelper;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\RouteInterface;
use Softspring\CmsBundle\Model\RoutePathInterface;
use Softspring\CmsBundle\Routing\SiteResolver;
use Softspring\CmsBundle\Routing\UrlGenerator;
use Softspring\CmsBundle\Routing\UrlMatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class RoutingHelperTest extends TestCase
{
    public function testGeneratesRoutePathAlternatesForCurrentAndAlternateSites(): void
    {
        $defaultSite = $this->createSite('default', ['es', 'en']);
        $blogSite = $this->createSite('blog', ['en']);

        $spanishPath = $this->createRoutePath('es');
        $englishPath = $this->createRoutePath('en');
        $route = $this->createRoute([$spanishPath, $englishPath], [$defaultSite, $blogSite]);
        $spanishPath->method('getRoute')->willReturn($route);

        $urlGenerator = $this->createMock(UrlGenerator::class);
        $urlGenerator->method('getUrlFixed')
            ->willReturnCallback(fn (RoutePathInterface $path, Site $site): string => sprintf('https://%s/%s', $site->getId(), $path->getLocale()));

        $helper = $this->createRoutingHelper(urlGenerator: $urlGenerator);

        self::assertSame([
            ['@rel' => 'alternate', '@href' => 'https://default/es', '@hreflang' => 'es'],
            ['@rel' => 'alternate', '@href' => 'https://default/en', '@hreflang' => 'en'],
            ['@rel' => 'alternate', '@href' => 'https://blog/en', '@hreflang' => 'en'],
        ], $helper->generateRoutePathAlternates($spanishPath, $defaultSite));
    }

    public function testCanDisableLocaleAndSiteAlternates(): void
    {
        $site = $this->createSite('default', ['en']);
        $route = $this->createRoute([$this->createRoutePath('en')], [$site]);
        $path = $this->createRoutePath('en');
        $path->method('getRoute')->willReturn($route);

        $helper = $this->createRoutingHelper();

        self::assertSame([], $helper->generateRoutePathAlternates($path, $site, false, false));
    }

    public function testResolvesRequestFromUrl(): void
    {
        $site = $this->createSite('default', ['en']);

        $siteResolver = $this->createMock(SiteResolver::class);
        $siteResolver->expects(self::once())
            ->method('resolveSiteAndHost')
            ->with(self::isInstanceOf(Request::class))
            ->willReturn(['default', $site, ['domain' => 'example.org'], null]);

        $urlMatcher = $this->createMock(UrlMatcher::class);
        $urlMatcher->expects(self::once())
            ->method('matchRequest')
            ->willReturn(['_route' => 'cms_route']);

        $request = $this->createRoutingHelper(siteResolver: $siteResolver, urlMatcher: $urlMatcher)
            ->resolveRequestFromUrl('https://example.org/en/about');

        self::assertInstanceOf(Request::class, $request);
        self::assertSame('default', $request->attributes->get('_site'));
        self::assertSame($site, $request->attributes->get('_sfs_cms_site'));
        self::assertSame(['domain' => 'example.org'], $request->attributes->get('_sfs_cms_site_host_config'));
        self::assertNull($request->attributes->get('_sfs_cms_site_path_config'));
        self::assertSame('cms_route', $request->attributes->get('_route'));
    }

    public function testReturnsNullWhenUrlCannotBeResolved(): void
    {
        $siteResolver = $this->createMock(SiteResolver::class);
        $siteResolver->method('resolveSiteAndHost')->willReturn([null, null, null, null]);

        self::assertNull($this->createRoutingHelper(siteResolver: $siteResolver)->resolveRequestFromUrl('https://example.org/'));
    }

    public function testGeneratesUrlsFromStringsArraysAndRouteObjects(): void
    {
        $request = Request::create('/');
        $request->setLocale('es');
        $requestStack = $this->createRequestStack($request);

        $router = $this->createMock(RouterInterface::class);
        $router->expects(self::exactly(3))
            ->method('generate')
            ->willReturnCallback(function (string $route, array $parameters, int $referenceType): string {
                return json_encode([$route, $parameters, $referenceType], JSON_THROW_ON_ERROR);
            });

        $routeObject = $this->createMock(RouteInterface::class);
        $routeObject->method('getId')->willReturn('cms_page');

        $helper = $this->createRoutingHelper(router: $router, requestStack: $requestStack);

        self::assertSame('["cms_home",{"_locale":"es"},0]', $helper->generateUrl('cms_home'));
        self::assertSame('["cms_search",{"_locale":"en","_site":"default","q":"softspring"},1]', $helper->generatePath([
            'route_name' => 'cms_search',
            'route_params' => ['q' => 'softspring'],
        ], 'en', 'default'));
        self::assertSame('["cms_page",{"_locale":"fr"},0]', $helper->generateUrl($routeObject, 'fr'));
        self::assertSame('#', $helper->generateUrl(null));
        self::assertSame('#', $helper->generateUrl(['route_name' => null]));
    }

    public function testGeneratesUrlForFirstContentRouteWithRequestedLocale(): void
    {
        $missingRoute = $this->createMock(RouteInterface::class);
        $missingRoute->method('getPathForLocale')->with('es')->willReturn(null);

        $validRoute = $this->createMock(RouteInterface::class);
        $validRoute->method('getPathForLocale')->with('es')->willReturn($this->createRoutePath('es'));
        $validRoute->method('getId')->willReturn('cms_page');

        $content = $this->createMock(ContentInterface::class);
        $content->method('getRoutes')->willReturn(new ArrayCollection([$missingRoute, $validRoute]));

        $router = $this->createMock(RouterInterface::class);
        $router->expects(self::once())
            ->method('generate')
            ->with('cms_page', ['_locale' => 'es'], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('https://example.org/es/page');

        self::assertSame('https://example.org/es/page', $this->createRoutingHelper(router: $router)->generateUrlForContent($content, 'es'));
    }

    public function testReturnsHashWhenContentHasNoRouteForLocale(): void
    {
        $content = $this->createMock(ContentInterface::class);
        $content->method('getRoutes')->willReturn(new ArrayCollection([]));

        self::assertSame('#', $this->createRoutingHelper()->generateUrlForContent($content, 'es'));
    }

    private function createRoutingHelper(
        ?RouterInterface $router = null,
        ?UrlGenerator $urlGenerator = null,
        ?SiteResolver $siteResolver = null,
        ?UrlMatcher $urlMatcher = null,
        ?RequestStack $requestStack = null,
    ): RoutingHelper {
        return new RoutingHelper(
            $router ?? $this->createMock(RouterInterface::class),
            $urlGenerator ?? $this->createMock(UrlGenerator::class),
            $siteResolver ?? $this->createMock(SiteResolver::class),
            $urlMatcher ?? $this->createMock(UrlMatcher::class),
            $requestStack ?? new RequestStack(),
        );
    }

    private function createRequestStack(Request $request): RequestStack
    {
        $requestStack = new RequestStack();
        Closure::fromCallable([$requestStack, 'push'])($request);

        return $requestStack;
    }

    private function createSite(string $id, array $locales): Site
    {
        $site = new Site();
        $site->setId($id);
        $site->setConfig([
            'locales' => $locales,
            'extra' => [],
        ]);

        return $site;
    }

    /**
     * @return RoutePathInterface&MockObject
     */
    private function createRoutePath(string $locale): RoutePathInterface
    {
        $path = $this->createMock(RoutePathInterface::class);
        $path->method('getLocale')->willReturn($locale);

        return $path;
    }

    /**
     * @return RouteInterface&MockObject
     */
    private function createRoute(array $paths, array $sites): RouteInterface
    {
        $route = $this->createMock(RouteInterface::class);
        $route->method('getPaths')->willReturn(new ArrayCollection($paths));
        $route->method('getSites')->willReturn(new ArrayCollection($sites));

        return $route;
    }
}
