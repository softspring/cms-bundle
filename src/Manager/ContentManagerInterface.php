<?php

namespace Softspring\CmsBundle\Manager;

use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CrudlBundle\Manager\CrudlEntityManagerInterface;

interface ContentManagerInterface extends CrudlEntityManagerInterface
{
    /**
     * @return ContentInterface
     */
    public function createEntity();

    /**
     * @param ContentInterface $entity
     */
    public function saveEntity($entity): void;

    /**
     * @param ContentInterface $entity
     */
    public function deleteEntity($entity): void;

    public function createVersion(ContentInterface $content): ContentVersionInterface;
}
