<?php

namespace Softspring\CmsBundle\EntityListener;

use Doctrine\ORM\Mapping\MappingException;
use Doctrine\Persistence\ObjectManager;

trait TransformEntityValuesTrait
{
    protected function transformEntityValues($value, ObjectManager $objectManager)
    {
        if (is_object($value)) {
            try {
                return [
                    '_entity_class' => get_class($value),
                    '_entity_id' => $objectManager->getClassMetadata(get_class($value))->getIdentifierValues($value),
                ];
            } catch (MappingException $e) {
            }
        } elseif (is_array($value)) {
            foreach ($value as $key => $value2) {
                $value[$key] = $this->transformEntityValues($value2, $objectManager);
            }
        }

        return $value;
    }

    protected function untransformEntityValues($value, ObjectManager $objectManager)
    {
        if (is_array($value)) {
            if (isset($value['_entity_class'])) {
                $repo = $objectManager->getRepository($value['_entity_class']);

                return $repo->findOneBy($value['_entity_id']);
            } else {
                foreach ($value as $key => $value2) {
                    $value[$key] = $this->untransformEntityValues($value2, $objectManager);
                }
            }
        }

        return $value;
    }
}
