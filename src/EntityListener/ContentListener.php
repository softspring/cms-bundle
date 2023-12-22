<?php

namespace Softspring\CmsBundle\EntityListener;

use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Softspring\CmsBundle\EntityTransformer\ContentTransformer;
use Softspring\CmsBundle\EntityTransformer\UnsupportedException;
use Softspring\CmsBundle\Model\ContentInterface;

class ContentListener
{
    protected ContentTransformer $contentTransformer;

    public function __construct(ContentTransformer $contentTransformer)
    {
        $this->contentTransformer = $contentTransformer;
    }

    /**
     * @throws UnsupportedException
     */
    public function postLoad(ContentInterface $content, PostLoadEventArgs $event): void
    {
        $this->contentTransformer->untransform($content, $event->getObjectManager());
    }

    /**
     * @throws UnsupportedException
     */
    public function preUpdate(ContentInterface $content, PreUpdateEventArgs $event): void
    {
        $this->contentTransformer->transform($content, $event->getObjectManager());
    }

    /**
     * @throws UnsupportedException
     */
    public function prePersist(ContentInterface $content, PrePersistEventArgs $event): void
    {
        $this->contentTransformer->transform($content, $event->getObjectManager());
    }
}
