<?php

namespace Softspring\CmsBundle\Manager;

use Softspring\CmsBundle\Model\MenuInterface;
use Softspring\Component\CrudlController\Manager\CrudlEntityManagerInterface;

interface MenuManagerInterface extends CrudlEntityManagerInterface
{
    /**
     * @return MenuInterface
     */
    public function createEntity(?string $menuType = null): object;

    /**
     * @psalm-param MenuInterface $entity
     */
    public function saveEntity(object $entity): void;

    /**
     * @psalm-param MenuInterface $entity
     */
    public function deleteEntity(object $entity): void;
}
