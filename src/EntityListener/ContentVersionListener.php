<?php

namespace Softspring\CmsBundle\EntityListener;

use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\Transformer\ContentVersionTransformer;

class ContentVersionListener
{
    protected ContentVersionTransformer $contentVersionTransformer;

    public function __construct(ContentVersionTransformer $contentVersionTransformer)
    {
        $this->contentVersionTransformer = $contentVersionTransformer;
    }

    public function postLoad(ContentVersionInterface $contentVersion, PostLoadEventArgs $event): void
    {
        $this->contentVersionTransformer->untransform($contentVersion, $event->getObjectManager());
    }

    public function preUpdate(ContentVersionInterface $contentVersion, PreUpdateEventArgs $event): void
    {
        $this->contentVersionTransformer->transform($contentVersion, $event->getObjectManager());
    }

    public function prePersist(ContentVersionInterface $contentVersion, PrePersistEventArgs $event): void
    {
        $this->contentVersionTransformer->transform($contentVersion, $event->getObjectManager());
    }
}
