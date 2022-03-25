<?php

namespace Softspring\CmsBundle\Manager;

use Softspring\CmsBundle\Model\RoutePathInterface;
use Softspring\CrudlBundle\Manager\CrudlEntityManagerInterface;

interface RoutePathManagerInterface extends CrudlEntityManagerInterface
{
    /**
     * @return RoutePathInterface
     */
    public function createEntity(): object;

    /**
     * @param RoutePathInterface $entity
     */
    public function saveEntity(object $entity): void;

    /**
     * @param RoutePathInterface $entity
     */
    public function deleteEntity(object $entity): void;
}
