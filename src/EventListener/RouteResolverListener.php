<?php

namespace Softspring\CmsBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Softspring\CmsBundle\Model\RoutePathInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RouteResolverListener implements EventSubscriberInterface
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 33]], // before Symfony\Component\HttpKernel\EventListener\RouterListener (32)
        ];
    }

    public function onKernelRequest (RequestEvent $event): void
    {
        $request = $event->getRequest();

        // TODO mirar el Symfony\Component\HttpKernel\EventListener\RouterListener para buscar cosas necesarias de incluir

        if ($request->attributes->has('_controller')) {
            // routing is already done
            return;
        }

        // search in database or redis-cache ;)
        if ($routePath = $this->searchRoutePath($request->getPathInfo())) {
            $request->attributes->set('_controller', 'Softspring\CmsBundle\Controller\ContentRenderer::contentRender');
            $request->attributes->set('_route', $routePath->getRoute()->getId());
            $request->attributes->set('_route_params', []);
            $request->attributes->set('routePath', $routePath);

            if ($routePath->getLocale()) {
                $request->setLocale($routePath->getLocale());
            }
        }
    }

    protected function searchRoutePath(string $path): ?RoutePathInterface
    {
        return $this->em->getRepository(RoutePathInterface::class)->findOneByPath($path);
    }
}