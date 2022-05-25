<?php

namespace Softspring\CmsBundle\EntityListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping\MappingException;
use Doctrine\Persistence\ObjectManager;
use Softspring\CmsBundle\Model\ContentInterface;

class ContentListener
{
    public function postLoad(ContentInterface $content, LifecycleEventArgs $event)
    {
        $this->untransform($content, $event);
    }

    public function preUpdate(ContentInterface $content, PreUpdateEventArgs $event)
    {
        $this->transform($content, $event);
    }

    public function prePersist(ContentInterface $content, LifecycleEventArgs $event)
    {
        $this->transform($content, $event);
    }

    public function transform(ContentInterface $content, LifecycleEventArgs $event)
    {
        if (!$content->getExtraData()) {
            return;
        }

        $extraData = $content->getExtraData();
        foreach ($extraData as $field => $value) {
            $extraData[$field] = $this->transformExtraDataValue($value, $event->getObjectManager());
        }
        $content->setExtraData($extraData);
    }

    public function untransform(ContentInterface $content, LifecycleEventArgs $event)
    {
        if (!$content->getExtraData()) {
            return;
        }

        $extraData = $content->getExtraData();
        foreach ($extraData as $field => $value) {
            $extraData[$field] = $this->untransformExtraDataValue($value, $event->getObjectManager());
        }
        $content->setExtraData($extraData);
    }

    protected function transformExtraDataValue($value, ObjectManager $objectManager)
    {
        if (is_object($value)) {
            try {
                return [
                    '_entity_class' => get_class($value),
                    '_entity_id' => $objectManager->getClassMetadata(get_class($value))->getIdentifierValues($value),
                ];
            } catch (MappingException $e) {
            }
        }

        return $value;
    }

    protected function untransformExtraDataValue($value, ObjectManager $objectManager)
    {
        if (is_array($value) && isset($value['_entity_class'])) {
            $repo = $objectManager->getRepository($value['_entity_class']);

            return $repo->findOneBy($value['_entity_id']);
        }

        return $value;
    }
}
