<?php

namespace Softspring\CmsBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Softspring\CmsBundle\Entity\RoutePath;
use Softspring\CmsBundle\Model\RouteInterface;
use Softspring\Component\CrudlController\Manager\CrudlEntityManagerTrait;

class RouteManager implements RouteManagerInterface
{
    use CrudlEntityManagerTrait;

    protected EntityManagerInterface $em;
    protected RoutePathManagerInterface $routePathManager;

    public function __construct(EntityManagerInterface $em, RoutePathManagerInterface $routePathManager)
    {
        $this->em = $em;
        $this->routePathManager = $routePathManager;
    }

    public function createEntity(bool $addOnePath = true): object
    {
        $class = $this->getEntityClass();
        /** @var RouteInterface $route */
        $route = new $class();

        $addOnePath && $route->addPath(new RoutePath());

        return $route;
    }

    public function getTargetClass(): string
    {
        return RouteInterface::class;
    }

    public function getRoutePathManager(): RoutePathManagerInterface
    {
        return $this->routePathManager;
    }
}
