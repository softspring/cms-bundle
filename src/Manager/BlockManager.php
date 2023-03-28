<?php

namespace Softspring\CmsBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Softspring\CmsBundle\Model\BlockInterface;
use Softspring\Component\CrudlController\Manager\CrudlEntityManagerTrait;

class BlockManager implements BlockManagerInterface
{
    use CrudlEntityManagerTrait;

    protected EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function createEntity(string $blockType = null): object
    {
        $class = $this->getEntityClass();
        /** @var BlockInterface $block */
        $block = new $class();
        $block->setType($blockType);

        return $block;
    }

    public function getTargetClass(): string
    {
        return BlockInterface::class;
    }
}
