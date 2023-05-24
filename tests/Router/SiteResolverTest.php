<?php

namespace Softspring\CmsBundle\Test\Config\Router;

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
    protected CmsConfig $cmsConfig;
    protected SiteManagerInterface $siteManager;

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

        $sites = array_map(function(array $siteConfig) {
            $site = new Site();
            $site->setId($siteConfig['_id']);
            $site->setConfig($siteConfig);
            return $site;
        }, $sitesConfig);

        $repository = $this->createMock(EntityRepository::class);
        $repository->method('findAll')->willReturn($sites);

        $this->siteManager = $this->createMock(SiteManager::class);
        $this->siteManager->method('getRepository')->willReturn($repository);
        $this->siteManager->method('createEntity')->willReturnCallback(function () { return new Site(); });

        $this->cmsConfig = new CmsConfig([], [], [], [], [], $sitesConfig, $this->siteManager);
    }

    public function testResolveWithHost()
    {
        $sitesConfig = ['identification' => 'domain'];
        $siteResolver = new SiteResolver($this->cmsConfig, $sitesConfig);

        $request = new Request([], [], [], [], [], ['SERVER_NAME' => 'sfs-cms.org']);
        [$siteId, $siteConfig, $hostConfig] = $siteResolver->resolveSiteAndHost($request);

        $this->assertEquals('default', $siteId);
        $this->assertEquals('sfs-cms.org', $hostConfig['domain']);
    }

    public function testResolveNotFound()
    {
        $sitesConfig = ['identification' => 'domain', 'throw_not_found' => false];
        $siteResolver = new SiteResolver($this->cmsConfig, $sitesConfig);

        $request = new Request([], [], [], [], [], ['SERVER_NAME' => 'other-hostname.org']);
        [$siteId, $siteConfig, $hostConfig] = $siteResolver->resolveSiteAndHost($request);

        $this->assertNull($siteId);
        $this->assertNull($siteConfig);
        $this->assertNull($hostConfig);
    }

    public function testResolveNotFoundWithException()
    {
        $this->expectException(SiteNotFoundException::class);

        $sitesConfig = ['identification' => 'domain', 'throw_not_found' => true];
        $siteResolver = new SiteResolver($this->cmsConfig, $sitesConfig);

        $request = new Request([], [], [], [], [], ['SERVER_NAME' => 'other-hostname.org']);
        $siteResolver->resolveSiteAndHost($request);
    }

    public function testResolveWithPath()
    {
        $this->expectException(\Exception::class);

        $sitesConfig = ['identification' => 'path'];
        $siteResolver = new SiteResolver($this->cmsConfig, $sitesConfig);

        $request = new Request([], [], [], [], [], ['SERVER_NAME' => 'sfs-cms.org']);
        $siteResolver->resolveSiteAndHost($request);
    }

    public function testCanonicalUrl()
    {
        $sitesConfig = ['identification' => 'domain', 'throw_not_found' => true];
        $siteResolver = new SiteResolver($this->cmsConfig, $sitesConfig);
        $request = new Request([], [], [], [], [], ['SERVER_NAME' => 'www.sfs-cms.org']);
        $this->assertEquals('https://sfs-cms.org/', $siteResolver->getCanonicalRedirectUrl($this->cmsConfig->getSite('default'), $request));
    }

    public function testCanonicalUrlWithPathAndQueryString()
    {
        $sitesConfig = ['identification' => 'domain', 'throw_not_found' => true];
        $siteResolver = new SiteResolver($this->cmsConfig, $sitesConfig);
        $request = new Request([], [], [], [], [], ['SERVER_NAME' => 'www.sfs-cms.org', 'REQUEST_URI' => 'https://www.sfs-cms.org/test/url', 'QUERY_STRING' => 'with-params=1']);
        $this->assertEquals('https://sfs-cms.org/test/url?with-params=1', $siteResolver->getCanonicalRedirectUrl($this->cmsConfig->getSite('default'), $request));
    }

    public function testCanonicalUrlWithNoCanonicalConfig()
    {
        $this->expectException(SiteHasNotACanonicalHostException::class);

        $sitesConfig = ['identification' => 'domain', 'throw_not_found' => true];
        $siteResolver = new SiteResolver($this->cmsConfig, $sitesConfig);
        $request = new Request([], [], [], [], [], ['SERVER_NAME' => 'no_canonical.sfs-cms.org']);
        $siteResolver->getCanonicalRedirectUrl($this->cmsConfig->getSite('no_canonical'), $request);
    }
}
