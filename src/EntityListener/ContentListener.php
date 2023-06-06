<?php

namespace Softspring\CmsBundle\EntityListener;

use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Transformer\ContentTransformer;

class ContentListener
{
    protected ContentTransformer $contentTransformer;

    public function __construct(ContentTransformer $contentTransformer)
    {
        $this->contentTransformer = $contentTransformer;
    }

    public function postLoad(ContentInterface $content, PostLoadEventArgs $event): void
    {
        $this->contentTransformer->untransform($content, $event->getObjectManager());
    }

    public function preUpdate(ContentInterface $content, PreUpdateEventArgs $event): void
    {
        $this->contentTransformer->transform($content, $event->getObjectManager());
    }

    public function prePersist(ContentInterface $content, PrePersistEventArgs $event): void
    {
        $this->contentTransformer->transform($content, $event->getObjectManager());
    }
}
