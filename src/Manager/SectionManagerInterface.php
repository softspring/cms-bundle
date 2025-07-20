<?php

namespace Softspring\CmsBundle\Manager;

use Doctrine\ORM\EntityRepository;
use Softspring\CmsBundle\Model\SectionInterface;
use Softspring\CmsBundle\Model\SectionVersionInterface;
use Softspring\Component\CrudlController\Manager\CrudlEntityManagerInterface;

interface SectionManagerInterface extends CrudlEntityManagerInterface
{
    /**
     * @return SectionInterface
     */
    public function createEntity(): object;

    /**
     * @psalm-param SectionInterface $entity
     */
    public function saveEntity(object $entity): void;

    /**
     * @psalm-param SectionInterface $entity
     */
    public function deleteEntity(object $entity): void;

    public function duplicateEntity(SectionInterface $section): SectionInterface;

    public function createVersion(SectionInterface $section, ?SectionVersionInterface $prevVersion = null, ?int $origin = SectionVersionInterface::ORIGIN_UNKNOWN): SectionVersionInterface;

    public function getRepository(): EntityRepository;
}
