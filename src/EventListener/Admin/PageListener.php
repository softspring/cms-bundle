<?php

namespace Softspring\CmsBundle\EventListener\Admin;

use Softspring\CmsBundle\SfsCmsEvents;
use Softspring\CrudlBundle\Event\GetResponseEntityEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;

class PageListener implements EventSubscriberInterface
{
    protected RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SfsCmsEvents::ADMIN_PAGES_CREATE_SUCCESS => ['onPageChangeSuccess'],
            SfsCmsEvents::ADMIN_PAGES_UPDATE_SUCCESS => ['onPageChangeSuccess'],
            SfsCmsEvents::ADMIN_PAGES_SEO_SUCCESS => ['onPageChangeSuccess'],
        ];
    }

    public function onPageChangeSuccess(GetResponseEntityEvent $event): void
    {
        $event->setResponse(new RedirectResponse($this->router->generate('sfs_cms_admin_pages_details', ['page'=>$event->getEntity()])));
    }
}