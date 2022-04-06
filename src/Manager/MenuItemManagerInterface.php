<?php

namespace Softspring\CmsBundle\Manager;

use Softspring\CmsBundle\Model\MenuItemInterface;
use Softspring\Component\CrudlController\Manager\CrudlEntityManagerInterface;

interface MenuItemManagerInterface extends CrudlEntityManagerInterface
{
    /**
     * @return MenuItemInterface
     */
    public function createEntity(): object;

    /**
     * @param MenuItemInterface $entity
     */
    public function saveEntity(object $entity): void;

    /**
     * @param MenuItemInterface $entity
     */
    public function deleteEntity(object $entity): void;
}
