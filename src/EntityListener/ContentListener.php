<?php

namespace Softspring\CmsBundle\EntityListener;

use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Softspring\CmsBundle\Model\ContentInterface;

class ContentListener
{
    use TransformEntityValuesTrait;

    public function postLoad(ContentInterface $content, PostLoadEventArgs $event): void
    {
        $this->untransform($content, $event);
    }

    public function preUpdate(ContentInterface $content, PreUpdateEventArgs $event): void
    {
        $this->transform($content, $event);
    }

    public function prePersist(ContentInterface $content, PrePersistEventArgs $event): void
    {
        $this->transform($content, $event);
    }

    public function transform(ContentInterface $content, LifecycleEventArgs $event): void
    {
        if (!$content->getExtraData()) {
            return;
        }

        $extraData = $content->getExtraData();
        foreach ($extraData as $field => $value) {
            $extraData[$field] = $this->transformEntityValues($value, $event->getObjectManager());
        }
        $content->setExtraData($extraData);
    }

    public function untransform(ContentInterface $content, LifecycleEventArgs $event): void
    {
        if (!$content->getExtraData()) {
            return;
        }

        $extraData = $content->getExtraData();
        foreach ($extraData as $field => $value) {
            $extraData[$field] = $this->untransformEntityValues($value, $event->getObjectManager());
        }
        $content->setExtraData($extraData);
    }
}
