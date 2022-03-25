<?php

namespace Softspring\CmsBundle\Manager;

use Softspring\CmsBundle\Model\RoutePathInterface;
use Softspring\CrudlBundle\Manager\CrudlEntityManagerInterface;

interface RoutePathManagerInterface extends CrudlEntityManagerInterface
{
    /**
     * @return RoutePathInterface
     */
    public function createEntity();

    /**
     * @param RoutePathInterface $entity
     */
    public function saveEntity($entity): void;

    /**
     * @param RoutePathInterface $entity
     */
    public function deleteEntity($entity): void;
}
