<?php

namespace Softspring\CmsBundle\EntityTransformer;

use Doctrine\ORM\Mapping\MappingException as ORMMappingException;
use Doctrine\Persistence\Mapping\MappingException as PersistenceMappingException;
use Doctrine\Persistence\ObjectManager;

trait TransformEntityValuesTrait
{
    protected function transformEntityValues($value, ObjectManager $objectManager, array &$entities = []): mixed
    {
        if (is_array($value) || is_iterable($value)) {
            foreach ($value as $key => $value2) {
                $value[$key] = $this->transformEntityValues($value2, $objectManager, $entities);
            }
        } elseif (is_object($value)) {
            try {
                $entities[] = $value;

                return [
                    '_entity_class' => get_class($value),
                    '_entity_id' => $objectManager->getClassMetadata(get_class($value))->getIdentifierValues($value),
                ];
            } catch (ORMMappingException|PersistenceMappingException $e) {
            }
        }

        return $value;
    }

    protected function untransformEntityValues($value, ObjectManager $objectManager): mixed
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
