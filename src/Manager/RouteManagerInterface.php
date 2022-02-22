<?php

namespace Softspring\CmsBundle\Manager;

use Softspring\CmsBundle\Model\RouteInterface;
use Softspring\CrudlBundle\Manager\CrudlEntityManagerInterface;

interface RouteManagerInterface extends CrudlEntityManagerInterface
{
    /**
     * @return RouteInterface
     */
    public function createEntity();

    /**
     * @param RouteInterface $entity
     */
    public function saveEntity($entity): void;

    /**
     * @param RouteInterface $entity
     */
    public function deleteEntity($entity): void;
}
