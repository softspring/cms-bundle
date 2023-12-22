<?php

namespace Softspring\CmsBundle\EntityListener;

use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Softspring\CmsBundle\EntityTransformer\BlockTransformer;
use Softspring\CmsBundle\EntityTransformer\UnsupportedException;
use Softspring\CmsBundle\Model\BlockInterface;

class BlockListener
{
    protected BlockTransformer $blockTransformer;

    public function __construct(BlockTransformer $blockTransformer)
    {
        $this->blockTransformer = $blockTransformer;
    }

    /**
     * @throws UnsupportedException
     */
    public function postLoad(BlockInterface $content, PostLoadEventArgs $event): void
    {
        $this->blockTransformer->untransform($content, $event->getObjectManager());
    }

    /**
     * @throws UnsupportedException
     */
    public function preUpdate(BlockInterface $content, PreUpdateEventArgs $event): void
    {
        $this->blockTransformer->transform($content, $event->getObjectManager());
    }

    /**
     * @throws UnsupportedException
     */
    public function prePersist(BlockInterface $content, PrePersistEventArgs $event): void
    {
        $this->blockTransformer->transform($content, $event->getObjectManager());
    }
}
