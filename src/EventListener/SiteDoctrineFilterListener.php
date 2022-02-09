<?php

namespace Softspring\CmsBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Softspring\CmsBundle\Doctrine\Filter\SiteFilter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class SiteDoctrineFilterListener implements EventSubscriberInterface
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var string
     */
    protected $siteRouteParamName;

    /**
     * SiteRequestListener constructor.
     */
    public function __construct(EntityManagerInterface $em, string $siteRouteParamName)
    {
        $this->em = $em;
        $this->siteRouteParamName = $siteRouteParamName;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [
                ['onRequestEnableDoctrineSiteFilter', -200],
            ],
        ];
    }

    /**
     * @param GetResponseEvent|RequestEvent $event
     */
    public function onRequestEnableDoctrineSiteFilter($event)
    {
        $request = $event->getRequest();

        if ($request->attributes->has($this->siteRouteParamName)) {
            $this->em->getConfiguration()->addFilter('site', SiteFilter::class);
            $filter = $this->em->getFilters()->enable('site');
            $filter->setParameter('_site', $request->attributes->get($this->siteRouteParamName));
        }
    }
}
