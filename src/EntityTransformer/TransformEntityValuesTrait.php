<?php

namespace Softspring\CmsBundle\EntityTransformer;

use Doctrine\ORM\Mapping\MappingException as ORMMappingException;
use Doctrine\Persistence\Mapping\MappingException as PersistenceMappingException;
use Doctrine\Persistence\ObjectManager;
use Softspring\CmsBundle\Model\RouteInterface;
use Softspring\TranslatableBundle\Model\Translation;

trait TransformEntityValuesTrait
{
    protected function transformEntityValues($value, ObjectManager $objectManager, array &$entities = []): mixed
    {
        if (is_array($value) || is_iterable($value)) {
            foreach ($value as $key => $value2) {
                $value[$key] = $this->transformEntityValues($value2, $objectManager, $entities);
            }

            if (($value['type'] ?? false) == 'route' && !empty($value['route_name'])) {
                $route = $objectManager->getRepository(RouteInterface::class)->findOneBy(['id' => $value['route_name']]);
                if ($route) {
                    $entities[] = $route;
                }
            }
        } elseif ($value instanceof Translation) {
            return $value->__toArray();
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
            } elseif (isset($value['_trans_id']) && isset($value['_default']) && isset($value[$value['_default']]) && (is_string($value[$value['_default']]) || is_null($value[$value['_default']]))) {
                // if we are sure that this is a translation, we can create a new Translation object
                return Translation::createFromArray($value);
            } else {
                foreach ($value as $key => $value2) {
                    $value[$key] = $this->untransformEntityValues($value2, $objectManager);
                }
            }
        }

        return $value;
    }
}
