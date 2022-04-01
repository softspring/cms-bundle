<?php

namespace Softspring\CmsBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Softspring\CmsBundle\Model\RoutePathInterface;
use Softspring\Component\CrudlController\Manager\CrudlEntityManagerTrait;

class RoutePathManager implements RoutePathManagerInterface
{
    use CrudlEntityManagerTrait;

    protected EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getTargetClass(): string
    {
        return RoutePathInterface::class;
    }
}
