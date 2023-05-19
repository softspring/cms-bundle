<?php

namespace Softspring\CmsBundle\Manager;

use Softspring\CmsBundle\Model\BlockInterface;
use Softspring\Component\CrudlController\Manager\CrudlEntityManagerInterface;

interface BlockManagerInterface extends CrudlEntityManagerInterface
{
    /**
     * @psalm-return BlockInterface
     */
    public function createEntity(string $blockType = null): object;

    /**
     * @psalm-param BlockInterface $entity
     */
    public function saveEntity(object $entity): void;

    /**
     * @psalm-param BlockInterface $entity
     */
    public function deleteEntity(object $entity): void;
}
