<?php

namespace Softspring\CmsBundle\Manager;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Softspring\CmsBundle\Model\CompiledDataInterface;
use Softspring\Component\CrudlController\Manager\CrudlEntityManagerTrait;

class CompiledDataManager implements CompiledDataManagerInterface
{
    use CrudlEntityManagerTrait;

    public function __construct(
        protected EntityManagerInterface $em,
        protected ?int $expirationTtl = 3600 * 24 * 30, // 30 days
    ) {
    }

    public function getTargetClass(): string
    {
        return CompiledDataInterface::class;
    }

    public function createEntity(): object
    {
        $class = $this->getEntityClass();
        /** @var CompiledDataInterface $entity */
        $entity = new $class();

        if ($this->expirationTtl) {
            $entity->setExpiresAt(new DateTime('now +'.$this->expirationTtl.' seconds'));
        }

        return $entity;
    }
}
