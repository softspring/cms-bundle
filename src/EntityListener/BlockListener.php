<?php

namespace Softspring\CmsBundle\EntityListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Softspring\CmsBundle\Model\BlockInterface;

class BlockListener
{
    use TransformEntityValuesTrait;

    public function postLoad(BlockInterface $content, LifecycleEventArgs $event)
    {
        $this->untransform($content, $event);
    }

    public function preUpdate(BlockInterface $content, PreUpdateEventArgs $event)
    {
        $this->transform($content, $event);
    }

    public function prePersist(BlockInterface $content, LifecycleEventArgs $event)
    {
        $this->transform($content, $event);
    }

    public function transform(BlockInterface $content, LifecycleEventArgs $event)
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

    public function untransform(BlockInterface $content, LifecycleEventArgs $event)
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
