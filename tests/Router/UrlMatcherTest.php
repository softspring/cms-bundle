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
use Softspring\CmsBundle\Entity\Site;
use Softspring\CmsBundle\Model\RouteInterface;
use Softspring\CmsBundle\Routing\SiteResolver;
use Softspring\CmsBundle\Routing\UrlGenerator;
use Softspring\CmsBundle\Routing\UrlMatcher;
use Symfony\Component\HttpFoundation\Request;

class UrlMatcherTest extends TestCase
{
    protected AbstractQuery $query;
    protected QueryBuilder $qb;
    protected EntityRepository $repository;
    protected EntityManagerInterface $em;
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
        $this->urlGenerator = $this->createMock(UrlGenerator::class);
        $this->siteResolver = $this->createMock(SiteResolver::class);
    }

    public function testNoSiteFound()
    {
        $urlMatcher = new UrlMatcher($this->em, $this->urlGenerator, $this->siteResolver);
        $request = new Request();
        $attributes = $urlMatcher->matchRequest($request);
        $this->assertIsArray($attributes);
        $this->assertEmpty($attributes);
    }

    public function testHttpsRedirect()
    {
        $siteConfig = ['https_redirect' => true];
        $site = new Site();
        $site->setId('default');
        $site->setConfig($siteConfig);
        $hostConfig = [];

        $this->siteResolver->method('getCanonicalRedirectUrl')->willReturn('https://example.org/redirect');

        $urlMatcher = new UrlMatcher($this->em, $this->urlGenerator, $this->siteResolver);
        $request = new Request();
        $request->attributes->set('_site', 'default');
        $request->attributes->set('_sfs_cms_site', $site);
        $request->attributes->set('_sfs_cms_site_host_config', $hostConfig);
        $attributes = $urlMatcher->matchRequest($request);

        $this->assertEquals([
            '_controller' => 'Softspring\CmsBundle\Controller\RedirectController::redirectToUrl',
            'url' => 'https://example.org/redirect',
            'statusCode' => 301,
        ], $attributes);
    }

    public function testRedirectToCanonical()
    {
        $siteConfig = ['https_redirect' => true];
        $site = new Site();
        $site->setId('default');
        $site->setConfig($siteConfig);
        $hostConfig = ['redirect_to_canonical' => true];

        $this->siteResolver->method('getCanonicalRedirectUrl')->willReturn('https://example.org/redirect');

        $urlMatcher = new UrlMatcher($this->em, $this->urlGenerator, $this->siteResolver);
        $request = new Request([], [], [], [], [], ['HTTPS' => true]);
        $request->attributes->set('_site', 'default');
        $request->attributes->set('_sfs_cms_site', $site);
        $request->attributes->set('_sfs_cms_site_host_config', $hostConfig);
        $attributes = $urlMatcher->matchRequest($request);
        $this->assertEquals([
            '_controller' => 'Softspring\CmsBundle\Controller\RedirectController::redirectToUrl',
            'url' => 'https://example.org/redirect',
            'statusCode' => 301,
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
        $site = new Site();
        $site->setId('default');
        $site->setConfig($siteConfig);
        $hostConfig = ['redirect_to_canonical' => false];

        $this->urlGenerator->method('getUrl')->willReturn('https://example.org/home-page');

        $urlMatcher = new UrlMatcher($this->em, $this->urlGenerator, $this->siteResolver);
        $request = new Request([], [], [], [], [], ['HTTPS' => true]);
        $request->attributes->set('_site', 'default');
        $request->attributes->set('_sfs_cms_site', $site);
        $request->attributes->set('_sfs_cms_site_host_config', $hostConfig);
        $attributes = $urlMatcher->matchRequest($request);
        $this->assertEquals([
            '_controller' => 'Softspring\CmsBundle\Controller\RedirectController::redirectToUrl',
            'url' => 'https://example.org/home-page',
            'statusCode' => 301,
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
        $site = new Site();
        $site->setId('default');
        $site->setConfig($siteConfig);
        $hostConfig = ['redirect_to_canonical' => false, 'locale' => 'es'];

        $this->query->method('getOneOrNullResult')->willReturn(null);

        $urlMatcher = new UrlMatcher($this->em, $this->urlGenerator, $this->siteResolver);
        $request = new Request([], [], [], [], [], ['HTTPS' => true]);
        $request->attributes->set('_site', 'default');
        $request->attributes->set('_sfs_cms_site', $site);
        $request->attributes->set('_sfs_cms_site_host_config', $hostConfig);
        $attributes = $urlMatcher->matchRequest($request);
        $this->assertEquals([
            '_sfs_cms_locale' => 'es',
            '_locale' => 'es',
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
                ['path' => '/es', 'locale' => 'es', 'trailing_slash_on_root' => false],
                ['path' => '/en', 'locale' => 'en', 'trailing_slash_on_root' => false],
            ],
            'sitemaps' => [],
            'sitemaps_index' => ['enabled' => false],
        ];
        $site = new Site();
        $site->setId('default');
        $site->setConfig($siteConfig);
        $hostConfig = ['redirect_to_canonical' => false, 'locale' => 'es'];

        $this->query->method('getOneOrNullResult')->willReturn(null);

        $urlMatcher = new UrlMatcher($this->em, $this->urlGenerator, $this->siteResolver);
        $request = new Request([], [], [], [], [], ['HTTPS' => true, 'SERVER_NAME' => 'sfs-cms.org', 'REQUEST_URI' => 'https://sfs-cms.org/en/test-url']);
        $request->attributes->set('_site', 'default');
        $request->attributes->set('_sfs_cms_site', $site);
        $request->attributes->set('_sfs_cms_site_host_config', $hostConfig);
        $attributes = $urlMatcher->matchRequest($request);
        $this->assertEquals([
            '_sfs_cms_locale' => 'en',
            '_locale' => 'en',
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
                ['path' => '/es', 'locale' => 'es', 'trailing_slash_on_root' => false],
                ['path' => '/en', 'locale' => 'en', 'trailing_slash_on_root' => false],
            ],
            'sitemaps' => [],
            'sitemaps_index' => ['enabled' => false],
        ];
        $site = new Site();
        $site->setId('default');
        $site->setConfig($siteConfig);
        $hostConfig = ['redirect_to_canonical' => false, 'locale' => 'es'];

        $route = new Route();
        $route->addSite($site);
        $route->setId('example');
        $route->setType(RouteInterface::TYPE_CONTENT);
        $route->setContent(new Page());
        $route->addPath($routePath = new RoutePath());
        $routePath->setLocale('fr');

        $this->query->method('getOneOrNullResult')->willReturn($routePath);

        $urlMatcher = new UrlMatcher($this->em, $this->urlGenerator, $this->siteResolver);
        $request = new Request([], [], [], [], [], ['HTTPS' => true, 'SERVER_NAME' => 'sfs-cms.org', 'REQUEST_URI' => 'https://sfs-cms.org/en/test-url']);
        $request->attributes->set('_site', 'default');
        $request->attributes->set('_sfs_cms_site', $site);
        $request->attributes->set('_sfs_cms_site_host_config', $hostConfig);
        $attributes = $urlMatcher->matchRequest($request);
        $this->assertEquals([
//            '_route' => 'cms#example',
//            '_route_params' => [],
//            '_controller' => 'Softspring\CmsBundle\Controller\ContentController::renderRoutePath',
//            'routePath' => $routePath,
            '_sfs_cms_locale' => 'en',
            '_locale' => 'en',
            '_sfs_cms_locale_path' => '/en',
        ], $attributes);
    }
}
