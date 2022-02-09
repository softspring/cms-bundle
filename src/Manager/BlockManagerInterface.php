<?php

namespace Softspring\CmsBundle\Manager;

use Softspring\CmsBundle\Model\BlockInterface;
use Softspring\CrudlBundle\Manager\CrudlEntityManagerInterface;

interface BlockManagerInterface extends CrudlEntityManagerInterface
{
    /**
     * @return BlockInterface
     */
    public function createEntity();

    /**
     * @param BlockInterface $entity
     */
    public function saveEntity($entity): void;

    /**
     * @param BlockInterface $entity
     */
    public function deleteEntity($entity): void;
}
