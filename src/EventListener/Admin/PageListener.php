<?php

namespace Softspring\CmsBundle\EventListener\Admin;

use Softspring\CmsBundle\SfsCmsEvents;
use Softspring\CrudlBundle\Event\GetResponseEntityEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;

class PageListener implements EventSubscriberInterface
{
    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            SfsCmsEvents::ADMIN_PAGES_CREATE_SUCCESS => ['onPageChangeSuccess'],
            SfsCmsEvents::ADMIN_PAGES_UPDATE_SUCCESS => ['onPageChangeSuccess'],
            SfsCmsEvents::ADMIN_PAGES_SEO_SUCCESS => ['onPageChangeSuccess'],
        ];
    }

    public function onPageChangeSuccess(GetResponseEntityEvent $event)
    {
        $event->setResponse(new RedirectResponse($this->router->generate('sfs_cms_admin_pages_details', ['page'=>$event->getEntity()])));
    }
}