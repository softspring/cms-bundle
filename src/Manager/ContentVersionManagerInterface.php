<?php

namespace Softspring\CmsBundle\Manager;

use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\Component\CrudlController\Manager\CrudlEntityManagerInterface;

interface ContentVersionManagerInterface extends CrudlEntityManagerInterface
{
    /**
     * @return ContentVersionInterface
     */
    public function createEntity(): object;

    /**
     * @psalm-param ContentVersionInterface $entity
     */
    public function saveEntity(object $entity): void;

    /**
     * @psalm-param ContentVersionInterface $entity
     */
    public function deleteEntity(object $entity): void;

    public function canSaveCompiled(ContentVersionInterface $version): bool;
}
