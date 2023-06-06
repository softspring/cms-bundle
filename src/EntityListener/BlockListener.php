<?php

namespace Softspring\CmsBundle\EntityListener;

use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Softspring\CmsBundle\Model\BlockInterface;
use Softspring\CmsBundle\Transformer\BlockTransformer;

class BlockListener
{
    protected BlockTransformer $blockTransformer;

    public function __construct(BlockTransformer $blockTransformer)
    {
        $this->blockTransformer = $blockTransformer;
    }

    public function postLoad(BlockInterface $content, PostLoadEventArgs $event): void
    {
        $this->blockTransformer->untransform($content, $event->getObjectManager());
    }

    public function preUpdate(BlockInterface $content, PreUpdateEventArgs $event): void
    {
        $this->blockTransformer->transform($content, $event->getObjectManager());
    }

    public function prePersist(BlockInterface $content, PrePersistEventArgs $event): void
    {
        $this->blockTransformer->transform($content, $event->getObjectManager());
    }
}
