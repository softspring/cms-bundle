<?php

namespace Softspring\CmsBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Softspring\CrudlBundle\Manager\CrudlEntityManagerTrait;
use Softspring\CmsBundle\Model\BlockInterface;

class BlockManager implements BlockManagerInterface
{
    use CrudlEntityManagerTrait;

    protected EntityManagerInterface $em;

    /**
     * BlockManager constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getTargetClass(): string
    {
        return BlockInterface::class;
    }
}