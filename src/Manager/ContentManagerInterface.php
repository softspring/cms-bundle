<?php

namespace Softspring\CmsBundle\Manager;

use Doctrine\ORM\EntityRepository;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\Component\CrudlController\Manager\CrudlEntityManagerInterface;

interface ContentManagerInterface extends CrudlEntityManagerInterface
{
    /**
     * @return ContentInterface
     */
    public function createEntity(?string $type = null): object;

    /**
     * @psalm-param ContentInterface $entity
     */
    public function saveEntity(object $entity): void;

    /**
     * @psalm-param ContentInterface $entity
     */
    public function deleteEntity(object $entity): void;

    public function createVersion(ContentInterface $content, ?ContentVersionInterface $prevVersion = null, ?int $origin = ContentVersionInterface::ORIGIN_UNKNOWN): ContentVersionInterface;

    public function getRepository(?string $type = null): EntityRepository;

    public function getTypeClass(?string $type = null): string;
}
