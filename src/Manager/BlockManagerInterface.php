<?php

namespace Softspring\CmsBundle\Manager;

use Softspring\CmsBundle\Model\BlockInterface;
use Softspring\Component\CrudlController\Manager\CrudlEntityManagerInterface;

interface BlockManagerInterface extends CrudlEntityManagerInterface
{
    /**
     * @return BlockInterface
     */
    public function createEntity(): object;

    /**
     * @param BlockInterface $entity
     */
    public function saveEntity(object $entity): void;

    /**
     * @param BlockInterface $entity
     */
    public function deleteEntity(object $entity): void;
}
