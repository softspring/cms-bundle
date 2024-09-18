<?php

namespace Softspring\CmsBundle\Manager;

use Softspring\CmsBundle\Model\CompiledDataInterface;
use Softspring\Component\CrudlController\Manager\CrudlEntityManagerInterface;

interface CompiledDataManagerInterface extends CrudlEntityManagerInterface
{
    /**
     * @return CompiledDataInterface
     */
    public function createEntity(): object;

    /**
     * @psalm-param CompiledDataInterface $entity
     */
    public function saveEntity(object $entity): void;

    /**
     * @psalm-param CompiledDataInterface $entity
     */
    public function deleteEntity(object $entity): void;
}
