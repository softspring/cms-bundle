<?php

namespace Softspring\CmsBundle\Manager;

use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CrudlBundle\Manager\CrudlEntityManagerInterface;

interface ContentVersionManagerInterface extends CrudlEntityManagerInterface
{
    /**
     * @return ContentVersionInterface
     */
    public function createEntity();

    /**
     * @param ContentVersionInterface $entity
     */
    public function saveEntity($entity): void;

    /**
     * @param ContentVersionInterface $entity
     */
    public function deleteEntity($entity): void;
}
