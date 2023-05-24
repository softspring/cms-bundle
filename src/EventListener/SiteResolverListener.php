<?php

namespace Softspring\CmsBundle\EventListener;

use Softspring\CmsBundle\Routing\SiteResolver;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class SiteResolverListener implements EventSubscriberInterface
{
    protected SiteResolver $siteResolver;

    public function __construct(SiteResolver $siteResolver)
    {
        $this->siteResolver = $siteResolver;
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

        [$siteId, $site, $siteHostConfig] = $this->siteResolver->resolveSiteAndHost($request);

        if (!$siteId) {
            return;
        }

        $request->attributes->set('_site', $siteId);
        $request->attributes->set('_sfs_cms_site', $site);
        $request->attributes->set('_sfs_cms_site_host_config', $siteHostConfig);
    }
}
