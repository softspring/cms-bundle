<?php

namespace Softspring\CmsBundle\Manager;

use Softspring\CmsBundle\Model\SiteInterface;
use Softspring\Component\CrudlController\Manager\CrudlEntityManagerInterface;

interface SiteManagerInterface extends CrudlEntityManagerInterface
{
    /**
     * @return SiteInterface
     */
    public function createEntity(): object;

    /**
     * @param SiteInterface $entity
     */
    public function saveEntity(object $entity): void;

    /**
     * @param SiteInterface $entity
     */
    public function deleteEntity(object $entity): void;
}
