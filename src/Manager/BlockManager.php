<?php

namespace Softspring\CmsBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Softspring\CmsBundle\Model\BlockInterface;
use Softspring\CrudlBundle\Manager\CrudlEntityManagerTrait;

class BlockManager implements BlockManagerInterface
{
    use CrudlEntityManagerTrait;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * BlockManager constructor.
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
