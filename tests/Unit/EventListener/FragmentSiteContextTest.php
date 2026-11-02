<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Test\Unit\EventListener;

use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Entity\Site;
use Softspring\CmsBundle\EventListener\SiteResolverListener;
use Softspring\CmsBundle\Exception\SiteNotFoundException;
use Softspring\CmsBundle\Routing\SiteResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class FragmentSiteContextTest extends TestCase
{
    private function resolver(): SiteResolver
    {
        $sites = [];
        foreach (['default' => 'example.test', 'secondary' => 'secondary.example.test', 'tertiary' => 'tertiary.example.test', 'quaternary' => 'quaternary.example.test', 'disabled' => 'disabled.example.test'] as $id => $host) {
            $site = new Site();
            $site->setId($id);
            $site->setConfig([
                'enabled' => 'disabled' !== $id,
                'hosts' => [['domain' => $host, 'canonical' => true, 'locale' => false, 'scheme' => 'https']],
                'paths' => [['path' => '/en', 'locale' => 'en'], ['path' => '/es', 'locale' => 'es']],
            ]);
            $sites[$id] = $site;
        }
        $config = $this->createMock(CmsConfig::class);
        $config->method('getSites')->willReturnCallback(static fn (bool $onlyEnabled = false): array => $onlyEnabled ? array_filter($sites, static fn (Site $site): bool => $site->isEnabled()) : $sites);

        return new SiteResolver($config, ['throw_not_found' => false]);
    }

    public function testStandaloneFragmentsRestoreEverySiteAndLocaleWithoutInventingAnOriginPage(): void
    {
        foreach (['default', 'secondary', 'tertiary', 'quaternary'] as $id) {
            $request = Request::create('https://example.test/_fragment');
            $request->attributes->add(['_controller' => 'menu', '_site' => $id, '_locale' => 'es']);
            $stack = new RequestStack();
            $stack->push($request);
            (new SiteResolverListener($this->resolver(), $stack))->onKernelRequest(new RequestEvent($this->createMock(HttpKernelInterface::class), $request, HttpKernelInterface::MAIN_REQUEST));

            self::assertSame($id, $request->attributes->get('_sfs_cms_site')->getId());
            self::assertSame('es', $request->getLocale());
            self::assertSame('/es', $request->attributes->get('_sfs_cms_site_path_config')['path']);
            self::assertFalse($request->attributes->has('_sfs_cms_origin_request'));
        }
    }

    public function testInlineFragmentRetainsItsRealOriginPage(): void
    {
        $main = Request::create('https://example.test/en/page');
        $request = Request::create('https://example.test/_fragment');
        $request->attributes->add(['_controller' => 'menu', '_site' => 'default', '_locale' => 'en']);
        $stack = new RequestStack();
        $stack->push($main);
        $stack->push($request);
        (new SiteResolverListener($this->resolver(), $stack))->onKernelRequest(new RequestEvent($this->createMock(HttpKernelInterface::class), $request, HttpKernelInterface::SUB_REQUEST));
        self::assertSame($main, $request->attributes->get('_sfs_cms_origin_request'));
    }

    public function testQueryParametersCannotOverrideTheSiteOfANormalPage(): void
    {
        $request = Request::create('https://example.test/en/page?_site=secondary');
        self::assertSame('default', $this->resolver()->resolveSiteAndHost($request)[0]);
    }

    public function testInvalidExplicitSiteDoesNotFallBackToAnotherSite(): void
    {
        $request = Request::create('https://example.test/_fragment');
        $request->attributes->add(['_controller' => 'menu', '_site' => 'missing']);
        $this->expectException(SiteNotFoundException::class);
        $this->resolver()->resolveSiteAndHost($request);
    }

    public function testDisabledExplicitSiteDoesNotFallBackToAnotherSite(): void
    {
        $request = Request::create('https://example.test/_fragment');
        $request->attributes->add(['_controller' => 'menu', '_site' => 'disabled']);
        $this->expectException(SiteNotFoundException::class);
        $this->resolver()->resolveSiteAndHost($request);
    }
}
