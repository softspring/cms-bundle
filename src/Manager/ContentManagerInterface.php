<?php

namespace Softspring\CmsBundle\Manager;

use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\Component\CrudlController\Manager\CrudlEntityManagerInterface;

interface ContentManagerInterface extends CrudlEntityManagerInterface
{
    /**
     * @return ContentInterface
     */
    public function createEntity(): object;

    /**
     * @param ContentInterface $entity
     */
    public function saveEntity(object $entity): void;

    /**
     * @param ContentInterface $entity
     */
    public function deleteEntity(object $entity): void;

    public function createVersion(ContentInterface $content): ContentVersionInterface;
}
