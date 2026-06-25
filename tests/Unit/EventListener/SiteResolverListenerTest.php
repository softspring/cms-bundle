<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Test\Unit\EventListener;

use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Entity\Site;
use Softspring\CmsBundle\EventListener\SiteResolverListener;
use Softspring\CmsBundle\Routing\SiteResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\KernelInterface;

class SiteResolverListenerTest extends TestCase
{
    public function testSubscribedEventsRunBeforeTheRouterListener(): void
    {
        self::assertSame([[['onKernelRequest', 40]]], array_values(SiteResolverListener::getSubscribedEvents()));
        self::assertArrayHasKey(KernelEvents::REQUEST, SiteResolverListener::getSubscribedEvents());
    }

    public function testItAddsResolvedSiteAttributesAndOriginRequest(): void
    {
        $mainRequest = Request::create('/main');
        $requestStack = new RequestStack();
        $requestStack->push($mainRequest);

        $site = new Site();
        $site->setId('main');
        $hostConfig = ['domain' => 'example.org'];
        $pathConfig = ['path' => '/es'];

        $siteResolver = $this->createMock(SiteResolver::class);
        $siteResolver->expects($this->once())
            ->method('resolveSiteAndHost')
            ->willReturn(['main', $site, $hostConfig, $pathConfig]);

        $listener = new SiteResolverListener($siteResolver, $requestStack);
        $request = Request::create('https://example.org/es/page');

        $listener->onKernelRequest($this->createRequestEvent($request));

        self::assertSame('main', $request->attributes->get('_site'));
        self::assertSame($site, $request->attributes->get('_sfs_cms_site'));
        self::assertSame($hostConfig, $request->attributes->get('_sfs_cms_site_host_config'));
        self::assertSame($pathConfig, $request->attributes->get('_sfs_cms_site_path_config'));
        self::assertSame($mainRequest, $request->attributes->get('_sfs_cms_origin_request'));
    }

    public function testItLeavesRequestUntouchedWhenNoSiteIsResolved(): void
    {
        $siteResolver = $this->createStub(SiteResolver::class);
        $siteResolver->method('resolveSiteAndHost')->willReturn([null, null, null, null]);

        $request = Request::create('https://example.org/es/page');
        $listener = new SiteResolverListener($siteResolver, new RequestStack());
        $listener->onKernelRequest($this->createRequestEvent($request));

        self::assertFalse($request->attributes->has('_site'));
        self::assertFalse($request->attributes->has('_sfs_cms_origin_request'));
    }

    private function createRequestEvent(Request $request): RequestEvent
    {
        return new RequestEvent(
            $this->createStub(KernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );
    }
}
