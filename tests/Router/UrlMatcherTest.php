<?php

namespace Softspring\CmsBundle\Test\Config\Router;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Entity\Page;
use Softspring\CmsBundle\Entity\Route;
use Softspring\CmsBundle\Entity\RoutePath;
use Softspring\CmsBundle\Model\RouteInterface;
use Softspring\CmsBundle\Router\SiteResolver;
use Softspring\CmsBundle\Router\UrlGenerator;
use Softspring\CmsBundle\Router\UrlMatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

class UrlMatcherTest extends TestCase
{
    protected AbstractQuery $query;
    protected QueryBuilder $qb;
    protected EntityRepository $repository;
    protected EntityManagerInterface $em;
    protected RouterInterface $router;
    protected UrlGenerator $urlGenerator;
    protected SiteResolver $siteResolver;

    protected function setUp(): void
    {
        $this->query = $this->createMock(AbstractQuery::class);

        $this->qb = $this->createMock(QueryBuilder::class);
        $this->qb->method('getQuery')->willReturn($this->query);

        $this->repository = $this->createMock(EntityRepository::class);
        $this->repository->method('createQueryBuilder')->willReturn($this->qb);
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->em->method('getRepository')->willReturn($this->repository);
        $this->router = $this->createMock(RouterInterface::class);
        $this->urlGenerator = $this->createMock(UrlGenerator::class);
        $this->siteResolver = $this->createMock(SiteResolver::class);
    }

    public function testNoSiteFound()
    {
        $urlMatcher = new UrlMatcher($this->em, $this->router, $this->urlGenerator, $this->siteResolver);
        $request = new Request();
        $attributes = $urlMatcher->matchRequest($request);
        $this->assertIsArray($attributes);
        $this->assertEmpty($attributes);
    }

    public function testHttpsRedirect()
    {
        $siteConfig = ['https_redirect' => true];
        $hostConfig = [];

        $this->siteResolver->method('resolveSiteAndHost')->willReturn(['default', $siteConfig, $hostConfig]);
        $this->siteResolver->method('getCanonicalRedirectUrl')->willReturn('https://example.org/redirect');

        $urlMatcher = new UrlMatcher($this->em, $this->router, $this->urlGenerator, $this->siteResolver);
        $request = new Request();
        $attributes = $urlMatcher->matchRequest($request);
        $this->assertEquals([
            '_sfs_cms_redirect' => 'https://example.org/redirect',
            '_sfs_cms_redirect_code' => 302,
        ], $attributes);
    }

    public function testRedirectToCanonical()
    {
        $siteConfig = ['https_redirect' => true];
        $hostConfig = ['redirect_to_canonical' => true];

        $this->siteResolver->method('resolveSiteAndHost')->willReturn(['default', $siteConfig, $hostConfig]);
        $this->siteResolver->method('getCanonicalRedirectUrl')->willReturn('https://example.org/redirect');

        $urlMatcher = new UrlMatcher($this->em, $this->router, $this->urlGenerator, $this->siteResolver);
        $request = new Request([], [], [], [], [], ['HTTPS' => true]);
        $attributes = $urlMatcher->matchRequest($request);
        $this->assertEquals([
            '_sfs_cms_redirect' => 'https://example.org/redirect',
            '_sfs_cms_redirect_code' => 302,
        ], $attributes);
    }

    public function testSlashRoute()
    {
        $siteConfig = [
            'locales' => ['es', 'en'],
            'https_redirect' => true,
            'slash_route' => [
                'enabled' => true,
                'behaviour' => 'redirect_to_route_with_user_language',
                'route' => 'home_page',
                'redirect_code' => 301,
            ],
            'sitemaps' => [],
            'sitemaps_index' => ['enabled' => false],
        ];
        $hostConfig = ['redirect_to_canonical' => false];

        $this->siteResolver->method('resolveSiteAndHost')->willReturn(['default', $siteConfig, $hostConfig]);
        $this->urlGenerator->method('getUrl')->willReturn('https://example.org/home-page');

        $urlMatcher = new UrlMatcher($this->em, $this->router, $this->urlGenerator, $this->siteResolver);
        $request = new Request([], [], [], [], [], ['HTTPS' => true]);
        $attributes = $urlMatcher->matchRequest($request);
        $this->assertEquals([
            '_sfs_cms_redirect' => 'https://example.org/home-page',
            '_sfs_cms_redirect_code' => 301,
        ], $attributes);
    }

    public function testSiteButRouteNotFound()
    {
        $siteConfig = [
            'locales' => ['es', 'en'],
            'https_redirect' => true,
            'slash_route' => [
                'enabled' => false,
            ],
            'paths' => [],
            'sitemaps' => [],
            'sitemaps_index' => ['enabled' => false],
        ];
        $hostConfig = ['redirect_to_canonical' => false, 'locale' => 'es'];

        $this->siteResolver->method('resolveSiteAndHost')->willReturn(['default', $siteConfig, $hostConfig]);
        $this->query->method('getOneOrNullResult')->willReturn(null);

        $urlMatcher = new UrlMatcher($this->em, $this->router, $this->urlGenerator, $this->siteResolver);
        $request = new Request([], [], [], [], [], ['HTTPS' => true]);
        $attributes = $urlMatcher->matchRequest($request);
        $this->assertEquals([
            '_sfs_cms_site' => $siteConfig + ['id' => 'default'],
            '_sfs_cms_locale' => 'es',
        ], $attributes);
    }

    public function testSiteWithPathsButRouteNotFound()
    {
        $siteConfig = [
            'locales' => ['es', 'en'],
            'https_redirect' => true,
            'slash_route' => [
                'enabled' => false,
            ],
            'paths' => [
                ['path' => '/es', 'locale' => 'es'],
                ['path' => '/en', 'locale' => 'en'],
            ],
            'sitemaps' => [],
            'sitemaps_index' => ['enabled' => false],
        ];
        $hostConfig = ['redirect_to_canonical' => false, 'locale' => 'es'];

        $this->siteResolver->method('resolveSiteAndHost')->willReturn(['default', $siteConfig, $hostConfig]);
        $this->query->method('getOneOrNullResult')->willReturn(null);

        $urlMatcher = new UrlMatcher($this->em, $this->router, $this->urlGenerator, $this->siteResolver);
        $request = new Request([], [], [], [], [], ['HTTPS' => true, 'SERVER_NAME' => 'sfs-cms.org', 'REQUEST_URI' => 'https://sfs-cms.org/en/test-url']);
        $attributes = $urlMatcher->matchRequest($request);
        $this->assertEquals([
            '_sfs_cms_site' => $siteConfig + ['id' => 'default'],
            '_sfs_cms_locale' => 'en',
            '_sfs_cms_locale_path' => '/en',
        ], $attributes);
    }

    public function testFoundRouteContent()
    {
        $siteConfig = [
            'locales' => ['es', 'en'],
            'https_redirect' => true,
            'locale_path_redirect_if_empty' => true,
            'slash_route' => [
                'enabled' => false,
            ],
            'paths' => [
                ['path' => '/es', 'locale' => 'es'],
                ['path' => '/en', 'locale' => 'en'],
            ],
            'sitemaps' => [],
            'sitemaps_index' => ['enabled' => false],
        ];
        $hostConfig = ['redirect_to_canonical' => false, 'locale' => 'es'];

        $route = new Route();
        $route->setSite('default');
        $route->setId('example');
        $route->setType(RouteInterface::TYPE_CONTENT);
        $route->setContent(new Page());
        $route->addPath($routePath = new RoutePath());
        $routePath->setLocale('fr');

        $this->siteResolver->method('resolveSiteAndHost')->willReturn(['default', $siteConfig, $hostConfig]);
        $this->query->method('getOneOrNullResult')->willReturn($routePath);

        $urlMatcher = new UrlMatcher($this->em, $this->router, $this->urlGenerator, $this->siteResolver);
        $request = new Request([], [], [], [], [], ['HTTPS' => true, 'SERVER_NAME' => 'sfs-cms.org', 'REQUEST_URI' => 'https://sfs-cms.org/en/test-url']);
        $attributes = $urlMatcher->matchRequest($request);
        $this->assertEquals([
            '_sfs_cms_site' => $siteConfig + ['id' => 'default'],
//            '_route' => 'cms#example',
//            '_route_params' => [],
//            '_controller' => 'Softspring\CmsBundle\Controller\ContentController::renderRoutePath',
//            'routePath' => $routePath,
            '_sfs_cms_locale' => 'en',
            '_sfs_cms_locale_path' => '/en',
        ], $attributes);
    }
}
