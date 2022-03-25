<?php

namespace Softspring\CmsBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Softspring\CmsBundle\Model\Route;
use Softspring\CmsBundle\Model\RouteInterface;
use Softspring\CmsBundle\Model\RoutePathInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;

class RouteResolverListener implements EventSubscriberInterface
{
    protected EntityManagerInterface $em;
    protected RouterInterface $router;
//    protected string $siteType;

//    public function __construct(EntityManagerInterface $em, string $siteType)
//    {
//        $this->em = $em;
//        $this->siteType = $siteType;
//    }

    public function __construct(EntityManagerInterface $em, RouterInterface $router)
    {
        $this->em = $em;
        $this->router = $router;
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

        // search in database or redis-cache ;)
        if ($routePath = $this->searchRoutePath($request->getPathInfo())) {
            $route = $routePath->getRoute();

            switch ($route->getType()) {
                case RouteInterface::TYPE_CONTENT:
                    $request->attributes->set('_controller', 'Softspring\CmsBundle\Controller\ContentController::renderRoutePath');
                    $request->attributes->set('_route', $routePath->getRoute()->getId());
                    $request->attributes->set('_route_params', []);
                    $request->attributes->set('routePath', $routePath);

                    if ($routePath->getLocale()) {
                        $request->setLocale($routePath->getLocale());
                    }
                    break;

                case Route::TYPE_REDIRECT_TO_URL:
                    $event->setResponse(new RedirectResponse($route->getRedirectUrl(), $route->getRedirectType() ?? Response::HTTP_FOUND));
                    break;

                case Route::TYPE_REDIRECT_TO_ROUTE:
                    $event->setResponse(new RedirectResponse($this->router->generate($route->getSymfonyRoute()), $route->getRedirectType() ?? Response::HTTP_FOUND));
                    break;

                default:
                    throw new \Exception('Route type not yet implemented');
            }
        }
    }

    protected function searchRoutePath(string $path): ?RoutePathInterface
    {
        return $this->em->getRepository(RoutePathInterface::class)->findOneByPath($path);
    }
}
