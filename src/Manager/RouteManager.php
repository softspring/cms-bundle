<?php

namespace Softspring\CmsBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Softspring\CmsBundle\Entity\RoutePath;
use Softspring\CmsBundle\Model\RouteInterface;
use Softspring\CrudlBundle\Manager\CrudlEntityManagerTrait;

class RouteManager implements RouteManagerInterface
{
    use CrudlEntityManagerTrait;

    protected EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function createEntity()
    {
        $class = $this->getEntityClass();
        /** @var RouteInterface $route */
        $route = new $class();

        $route->addPath(new RoutePath());

        return $route;
    }

    public function getTargetClass(): string
    {
        return RouteInterface::class;
    }
}
