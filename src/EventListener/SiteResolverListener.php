<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\EventListener;

use Softspring\CmsBundle\Routing\SiteResolver;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class SiteResolverListener implements EventSubscriberInterface
{
    protected SiteResolver $siteResolver;
    protected RequestStack $requestStack;

    protected ?Request $originRequest = null;

    public function __construct(SiteResolver $siteResolver, RequestStack $requestStack)
    {
        $this->siteResolver = $siteResolver;
        $this->requestStack = $requestStack;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // after Symfony\Component\HttpKernel\EventListener\FragmentListener::onKernelRequest (48)
            KernelEvents::REQUEST => [['onKernelRequest', 40]],
            // before Symfony\Component\HttpKernel\EventListener\RouterListener (32)
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        [$siteId, $site, $siteHostConfig, $sitePathConfig] = $this->siteResolver->resolveSiteAndHost($request);

        if (!$siteId) {
            return;
        }

        if ($request->attributes->has('_controller') && $request->attributes->has('_locale')) {
            $request->setLocale($request->attributes->get('_locale'));
        }

        $request->attributes->set('_site', $siteId);
        $request->attributes->set('_sfs_cms_site', $site);
        $request->attributes->set('_sfs_cms_site_host_config', $siteHostConfig);
        $request->attributes->set('_sfs_cms_site_path_config', $sitePathConfig);

        $this->originRequest = $this->requestStack->getMainRequest();
        // A standalone signed fragment has site context, but no originating page.
        if ($this->originRequest instanceof Request && ($this->originRequest !== $request || !$request->attributes->has('_controller'))) {
            $request->attributes->set('_sfs_cms_origin_request', $this->originRequest);
        }
    }
}
