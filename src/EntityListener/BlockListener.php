<?php

namespace Softspring\CmsBundle\EntityListener;

use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Softspring\CmsBundle\Model\BlockInterface;

class BlockListener
{
    use TransformEntityValuesTrait;

    public function postLoad(BlockInterface $content, PostLoadEventArgs $event): void
    {
        $this->untransform($content, $event);
    }

    public function preUpdate(BlockInterface $content, PreUpdateEventArgs $event): void
    {
        $this->transform($content, $event);
    }

    public function prePersist(BlockInterface $content, PrePersistEventArgs $event): void
    {
        $this->transform($content, $event);
    }

    public function transform(BlockInterface $content, LifecycleEventArgs $event): void
    {
        if (!$content->getData()) {
            return;
        }

        $extraData = $content->getData();
        foreach ($extraData as $field => $value) {
            $extraData[$field] = $this->transformEntityValues($value, $event->getObjectManager());
        }
        $content->setData($extraData);
    }

    public function untransform(BlockInterface $content, LifecycleEventArgs $event): void
    {
        if (!$content->getData()) {
            return;
        }

        $extraData = $content->getData();
        foreach ($extraData as $field => $value) {
            $extraData[$field] = $this->untransformEntityValues($value, $event->getObjectManager());
        }
        $content->setData($extraData);
    }
}
