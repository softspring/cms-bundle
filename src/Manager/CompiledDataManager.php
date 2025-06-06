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
        protected ?int $compiledDataExpirationTtl = null,
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

        if ($this->compiledDataExpirationTtl) {
            $entity->setExpiresAt(new DateTime('now +'.$this->compiledDataExpirationTtl.' seconds'));
        }

        return $entity;
    }
}
