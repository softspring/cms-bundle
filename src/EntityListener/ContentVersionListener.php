<?php

namespace Softspring\CmsBundle\EntityListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping\MappingException;
use Doctrine\Persistence\ObjectManager;
use Softspring\CmsBundle\Model\ContentVersionInterface;

class ContentVersionListener
{
    public function postLoad(ContentVersionInterface $contentVersion, LifecycleEventArgs $event)
    {
        $this->untransform($contentVersion, $event);
    }

    public function preUpdate(ContentVersionInterface $contentVersion, PreUpdateEventArgs $event)
    {
        $this->transform($contentVersion, $event);
    }

    public function prePersist(ContentVersionInterface $contentVersion, LifecycleEventArgs $event)
    {
        $this->transform($contentVersion, $event);
    }

    protected function transform(ContentVersionInterface $contentVersion, LifecycleEventArgs $event)
    {
        if (!$contentVersion->getData()) {
            return;
        }

        $data = $contentVersion->getData();
        foreach ($data as $layout => $modules) {
            foreach ($modules as $m => $module) {
                if (isset($module['modules'])) {
                    foreach ($module['modules'] as $sm => $submodule) {
                        foreach ($submodule as $field => $value) {
                            $data[$layout][$m]['modules'][$sm][$field] = $this->transformExtraDataValue($value, $event->getObjectManager());
                        }
                    }
                } else {
                    foreach ($module as $field => $value) {
                        $data[$layout][$m][$field] = $this->transformExtraDataValue($value, $event->getObjectManager());
                    }
                }
            }
        }
        $contentVersion->setData($data);
    }

    protected function untransform(ContentVersionInterface $contentVersion, LifecycleEventArgs $event)
    {
        if (!$contentVersion->getData()) {
            return;
        }

        $data = $contentVersion->getData();
        foreach ($data as $layout => $modules) {
            foreach ($modules as $m => $module) {
                if (isset($module['modules'])) {
                    foreach ($module['modules'] as $sm => $submodule) {
                        foreach ($submodule as $field => $value) {
                            $data[$layout][$m]['modules'][$sm][$field] = $this->untransformExtraDataValue($value, $event->getObjectManager());
                        }
                    }
                } else {
                    foreach ($module as $field => $value) {
                        $data[$layout][$m][$field] = $this->untransformExtraDataValue($value, $event->getObjectManager());
                    }
                }
            }
        }
        $contentVersion->setData($data);
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
