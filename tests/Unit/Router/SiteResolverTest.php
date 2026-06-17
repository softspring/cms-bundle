<?php

namespace Softspring\CmsBundle\Test\Unit\Config\Router;

use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Entity\Site;
use Softspring\CmsBundle\Exception\SiteNotFoundException;
use Softspring\CmsBundle\Manager\SiteManager;
use Softspring\CmsBundle\Manager\SiteManagerInterface;
use Softspring\CmsBundle\Routing\SiteResolver;
use Symfony\Component\HttpFoundation\Request;

class SiteResolverTest extends TestCase
{
    protected CmsConfig&MockObject $cmsConfig;
    protected SiteManagerInterface&MockObject $siteManager;

    protected function setUp(): void
    {
        $sitesConfig = [
            'default' => [
                '_id' => 'default',
                'hosts' => [
                    ['domain' => 'www.sfs-cms.org', 'locale' => false, 'scheme' => 'https', 'canonical' => false, 'redirect_to_canonical' => true],
                    ['domain' => 'sfs-cms.org', 'locale' => false, 'scheme' => 'https', 'canonical' => true, 'redirect_to_canonical' => false],
                ],
                'paths' => [
                    ['path' => '/es', 'locale' => 'es', 'trailing_slash_on_root' => true],
                    ['path' => '/en', 'locale' => 'en', 'trailing_slash_on_root' => true],
                ],
                'slash_route' => [
                    'enabled' => true,
                    'behaviour' => 'redirect_to_route_with_user_language',
                    'route' => 'home',
                    'redirect_code' => 301,
                ],
            ],
            'no_canonical' => [
                '_id' => 'no_canonical',
                'hosts' => [
                    ['domain' => 'no_canonical.sfs-cms.org', 'locale' => false, 'scheme' => 'https', 'canonical' => false, 'redirect_to_canonical' => false],
                ],
            ],
            'blog' => [
                '_id' => 'blog',
                'hosts' => [
                    ['domain' => 'sfs-cms.org', 'locale' => false, 'scheme' => 'https', 'canonical' => true, 'redirect_to_canonical' => false],
                    ['domain' => 'www.sfs-cms.org', 'locale' => false, 'scheme' => 'https', 'canonical' => false, 'redirect_to_canonical' => true],
                ],
                'paths' => [
                    ['path' => '/es/blog', 'locale' => 'es', 'trailing_slash_on_root' => true],
                    ['path' => '/en/blog', 'locale' => 'en', 'trailing_slash_on_root' => true],
                ],
            ],
            'store' => [
                '_id' => 'blog',
                'hosts' => [
                    ['domain' => 'tienda.sfs-cms.org', 'locale' => 'es', 'scheme' => 'https', 'canonical' => true, 'redirect_to_canonical' => false],
                    ['domain' => 'store.sfs-cms.org', 'locale' => 'en', 'scheme' => 'https', 'canonical' => true, 'redirect_to_canonical' => false],
                ],
            ],
            'docs' => [
                '_id' => 'docs',
                'hosts' => [],
                'paths' => [
                    ['path' => '/docs', 'locale' => 'en', 'trailing_slash_on_root' => true],
                ],
                'sitemaps' => [
                    ['url' => 'docs-sitemap.xml'],
                ],
                'sitemaps_index' => ['enabled' => true, 'url' => 'docs-sitemap-index.xml'],
                'robots' => ['mode' => 'static'],
            ],
        ];

        $sites = array_map(function (array $siteConfig): Site {
            $site = new Site();
            $site->setId($siteConfig['_id']);
            $site->setConfig($siteConfig);

            return $site;
        }, $sitesConfig);

        $repository = $this->createMock(EntityRepository::class);
        $repository->method('findAll')->willReturn($sites);

        $this->siteManager = $this->createMock(SiteManager::class);
        $this->siteManager->method('getRepository')->willReturn($repository);
        $this->siteManager->method('createEntity')->willReturnCallback(function (): Site {
            return new Site();
        });

        $this->cmsConfig = $this->createMock(CmsConfig::class);
        $this->cmsConfig->method('getSites')->willReturnCallback(function () use ($sitesConfig): array {
            return array_map(function (array $config): Site {
                $site = new Site();
                $site->setId($config['_id']);
                $site->setConfig($config);

                return $site;
            }, $sitesConfig);
        });
        $this->cmsConfig->method('getSite')->willReturnCallback(function ($siteName) use ($sitesConfig): Site {
            $site = new Site();
            $site->setId($sitesConfig[$siteName]['_id']);
            $site->setConfig($sitesConfig[$siteName]);

            return $site;
        });
    }

    public function testResolveWithHost(): void
    {
        $sitesConfig = [];
        $siteResolver = new SiteResolver($this->cmsConfig, $sitesConfig);

        $request = new Request([], [], [], [], [], ['SERVER_NAME' => 'sfs-cms.org', 'REQUEST_URI' => '/es/example']);
        [$siteId, $siteConfig, $hostConfig, $pathConfig] = $siteResolver->resolveSiteAndHost($request);

        $this->assertEquals('default', $siteId);
        $this->assertEquals('sfs-cms.org', $hostConfig['domain']);
        $this->assertEquals('/es', $pathConfig['path']);
    }

    public function testResolveNotFound(): void
    {
        $sitesConfig = ['throw_not_found' => false];
        $siteResolver = new SiteResolver($this->cmsConfig, $sitesConfig);

        $request = new Request([], [], [], [], [], ['SERVER_NAME' => 'other-hostname.org', 'REQUEST_URI' => '/es/example']);
        [$siteId, $siteConfig, $hostConfig, $pathConfig] = $siteResolver->resolveSiteAndHost($request);

        $this->assertNull($siteId);
        $this->assertNull($siteConfig);
        $this->assertNull($hostConfig);
        $this->assertNull($pathConfig);
    }

    public function testResolveNotFoundWithException(): void
    {
        $this->expectException(SiteNotFoundException::class);

        $sitesConfig = ['throw_not_found' => true];
        $siteResolver = new SiteResolver($this->cmsConfig, $sitesConfig);

        $request = new Request([], [], [], [], [], ['SERVER_NAME' => 'other-hostname.org', 'REQUEST_URI' => '/es/example']);
        $siteResolver->resolveSiteAndHost($request);
    }

    public function testResolveUsesMostSpecificPath(): void
    {
        $sitesConfig = [];
        $siteResolver = new SiteResolver($this->cmsConfig, $sitesConfig);

        $request = new Request([], [], [], [], [], ['SERVER_NAME' => 'sfs-cms.org', 'REQUEST_URI' => '/es/blog/example']);
        [$siteId, $siteConfig, $hostConfig, $pathConfig] = $siteResolver->resolveSiteAndHost($request);

        $this->assertEquals('blog', $siteId);
        $this->assertEquals('sfs-cms.org', $hostConfig['domain']);
        $this->assertEquals('/es/blog', $pathConfig['path']);
    }

    public function testResolveUsesLocalizedBlogPathOnMainDomain(): void
    {
        $siteResolver = new SiteResolver($this->cmsConfig, ['throw_not_found' => true]);

        $request = new Request([], [], [], [], [], ['SERVER_NAME' => 'sfs-cms.org', 'REQUEST_URI' => '/en/blog/example']);
        [$siteId, $siteConfig, $hostConfig, $pathConfig] = $siteResolver->resolveSiteAndHost($request);

        $this->assertEquals('blog', $siteId);
        $this->assertEquals('sfs-cms.org', $hostConfig['domain']);
        $this->assertEquals('/en/blog', $pathConfig['path']);
    }

    public function testResolvePathOnlySite(): void
    {
        $siteResolver = new SiteResolver($this->cmsConfig, ['throw_not_found' => true]);

        $request = new Request([], [], [], [], [], ['SERVER_NAME' => 'unknown-host.org', 'REQUEST_URI' => '/docs/install']);
        [$siteId, $siteConfig, $hostConfig, $pathConfig] = $siteResolver->resolveSiteAndHost($request);

        $this->assertEquals('docs', $siteId);
        $this->assertNull($hostConfig);
        $this->assertEquals('/docs', $pathConfig['path']);
    }

    public function testResolvePathRequiresSegmentBoundary(): void
    {
        $siteResolver = new SiteResolver($this->cmsConfig, ['throw_not_found' => false]);

        $request = new Request([], [], [], [], [], ['SERVER_NAME' => 'unknown-host.org', 'REQUEST_URI' => '/docs-and-guides']);
        [$siteId, $siteConfig, $hostConfig, $pathConfig] = $siteResolver->resolveSiteAndHost($request);

        $this->assertNull($siteId);
        $this->assertNull($siteConfig);
        $this->assertNull($hostConfig);
        $this->assertNull($pathConfig);
    }

    public function testResolveSitemapForSiteWithPathConfiguration(): void
    {
        $siteResolver = new SiteResolver($this->cmsConfig, ['throw_not_found' => true]);

        $request = new Request([], [], [], [], [], ['SERVER_NAME' => 'unknown-host.org', 'REQUEST_URI' => '/docs-sitemap.xml']);
        [$siteId, $siteConfig, $hostConfig, $pathConfig] = $siteResolver->resolveSiteAndHost($request);

        $this->assertEquals('docs', $siteId);
        $this->assertNull($hostConfig);
        $this->assertNull($pathConfig);
    }

    public function testResolveRobotsForSiteWithPathConfiguration(): void
    {
        $siteResolver = new SiteResolver($this->cmsConfig, ['throw_not_found' => true]);

        $request = new Request([], [], [], [], [], ['SERVER_NAME' => 'unknown-host.org', 'REQUEST_URI' => '/robots.txt']);
        [$siteId, $siteConfig, $hostConfig, $pathConfig] = $siteResolver->resolveSiteAndHost($request);

        $this->assertEquals('docs', $siteId);
        $this->assertNull($hostConfig);
        $this->assertNull($pathConfig);
    }

    public function testResolveSlashRouteForSiteWithPathConfiguration(): void
    {
        $siteResolver = new SiteResolver($this->cmsConfig, ['throw_not_found' => true]);

        $request = new Request([], [], [], [], [], ['SERVER_NAME' => 'sfs-cms.org', 'REQUEST_URI' => '/']);
        [$siteId, $siteConfig, $hostConfig, $pathConfig] = $siteResolver->resolveSiteAndHost($request);

        $this->assertEquals('default', $siteId);
        $this->assertEquals('sfs-cms.org', $hostConfig['domain']);
        $this->assertNull($pathConfig);
    }

    public function testCanonicalUrl(): void
    {
        $sitesConfig = ['throw_not_found' => true];
        $siteResolver = new SiteResolver($this->cmsConfig, $sitesConfig);
        $request = new Request([], [], [], [], [], ['SERVER_NAME' => 'www.sfs-cms.org']);
        $this->assertEquals('https://sfs-cms.org/', $siteResolver->getCanonicalRedirectUrl($this->cmsConfig->getSite('default'), $request));
    }

    public function testCanonicalUrlWithPathAndQueryString(): void
    {
        $sitesConfig = ['throw_not_found' => true];
        $siteResolver = new SiteResolver($this->cmsConfig, $sitesConfig);
        $request = new Request([], [], [], [], [], ['SERVER_NAME' => 'www.sfs-cms.org', 'REQUEST_URI' => 'https://www.sfs-cms.org/test/url', 'QUERY_STRING' => 'with-params=1']);
        $this->assertEquals('https://sfs-cms.org/test/url?with-params=1', $siteResolver->getCanonicalRedirectUrl($this->cmsConfig->getSite('default'), $request));
    }
}
