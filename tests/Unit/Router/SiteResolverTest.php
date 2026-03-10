<?php

namespace Softspring\CmsBundle\Test\Unit\Config\Router;

use PHPUnit\Framework\MockObject\MockObject;
use Exception;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Entity\Site;
use Softspring\CmsBundle\Exception\SiteHasNotACanonicalHostException;
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
                    ['domain' => 'blog.sfs-cms.org', 'locale' => false, 'scheme' => 'https', 'canonical' => true, 'redirect_to_canonical' => false],
                ],
            ],
            'store' => [
                '_id' => 'blog',
                'hosts' => [
                    ['domain' => 'tienda.sfs-cms.org', 'locale' => 'es', 'scheme' => 'https', 'canonical' => true, 'redirect_to_canonical' => false],
                    ['domain' => 'store.sfs-cms.org', 'locale' => 'en', 'scheme' => 'https', 'canonical' => true, 'redirect_to_canonical' => false],
                ],
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
        $sitesConfig = ['identification' => 'domain'];
        $siteResolver = new SiteResolver($this->cmsConfig, $sitesConfig);

        $request = new Request([], [], [], [], [], ['SERVER_NAME' => 'sfs-cms.org']);
        [$siteId, $siteConfig, $hostConfig] = $siteResolver->resolveSiteAndHost($request);

        $this->assertEquals('default', $siteId);
        $this->assertEquals('sfs-cms.org', $hostConfig['domain']);
    }

    public function testResolveNotFound(): void
    {
        $sitesConfig = ['identification' => 'domain', 'throw_not_found' => false];
        $siteResolver = new SiteResolver($this->cmsConfig, $sitesConfig);

        $request = new Request([], [], [], [], [], ['SERVER_NAME' => 'other-hostname.org']);
        [$siteId, $siteConfig, $hostConfig] = $siteResolver->resolveSiteAndHost($request);

        $this->assertNull($siteId);
        $this->assertNull($siteConfig);
        $this->assertNull($hostConfig);
    }

    public function testResolveNotFoundWithException(): void
    {
        $this->expectException(SiteNotFoundException::class);

        $sitesConfig = ['identification' => 'domain', 'throw_not_found' => true];
        $siteResolver = new SiteResolver($this->cmsConfig, $sitesConfig);

        $request = new Request([], [], [], [], [], ['SERVER_NAME' => 'other-hostname.org']);
        $siteResolver->resolveSiteAndHost($request);
    }

    public function testResolveWithPath(): void
    {
        $this->expectException(Exception::class);

        $sitesConfig = ['identification' => 'path'];
        $siteResolver = new SiteResolver($this->cmsConfig, $sitesConfig);

        $request = new Request([], [], [], [], [], ['SERVER_NAME' => 'sfs-cms.org']);
        $siteResolver->resolveSiteAndHost($request);
    }

    public function testCanonicalUrl(): void
    {
        $sitesConfig = ['identification' => 'domain', 'throw_not_found' => true];
        $siteResolver = new SiteResolver($this->cmsConfig, $sitesConfig);
        $request = new Request([], [], [], [], [], ['SERVER_NAME' => 'www.sfs-cms.org']);
        $this->assertEquals('https://sfs-cms.org/', $siteResolver->getCanonicalRedirectUrl($this->cmsConfig->getSite('default'), $request));
    }

    public function testCanonicalUrlWithPathAndQueryString(): void
    {
        $sitesConfig = ['identification' => 'domain', 'throw_not_found' => true];
        $siteResolver = new SiteResolver($this->cmsConfig, $sitesConfig);
        $request = new Request([], [], [], [], [], ['SERVER_NAME' => 'www.sfs-cms.org', 'REQUEST_URI' => 'https://www.sfs-cms.org/test/url', 'QUERY_STRING' => 'with-params=1']);
        $this->assertEquals('https://sfs-cms.org/test/url?with-params=1', $siteResolver->getCanonicalRedirectUrl($this->cmsConfig->getSite('default'), $request));
    }
}
