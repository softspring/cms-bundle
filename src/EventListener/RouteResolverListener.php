<?php

namespace Softspring\CmsBundle\EventListener;

use Softspring\CmsBundle\Router\UrlMatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RouteResolverListener implements EventSubscriberInterface
{
    protected UrlMatcher $urlMatcher;

    public function __construct(UrlMatcher $urlMatcher)
    {
        $this->urlMatcher = $urlMatcher;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 33]], // before Symfony\Component\HttpKernel\EventListener\RouterListener (32)
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        // TODO mirar el Symfony\Component\HttpKernel\EventListener\RouterListener para buscar cosas necesarias de incluir

        if ($request->attributes->has('_controller')) {
            // routing is already done
            return;
        }

        $attributes = $this->urlMatcher->matchRequest($request);

        if (isset($attributes['_sfs_cms_redirect'])) {
            $event->setResponse(new RedirectResponse($attributes['_sfs_cms_redirect'], $attributes['_sfs_cms_redirect_code'] ?? Response::HTTP_FOUND));
            return;
        }

        if (isset($attributes['locale'])) {
            $request->setLocale($attributes['locale']);
        }

        $request->attributes->add($attributes);
    }
}
