<?php

namespace Softspring\CmsBundle\EntityTransformer;

use Doctrine\ORM\Mapping\MappingException as ORMMappingException;
use Doctrine\Persistence\Mapping\MappingException as PersistenceMappingException;
use Doctrine\Persistence\ObjectManager;
use Softspring\CmsBundle\Model\RouteInterface;

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

    protected array $_references = [];

    protected function untransformEntityValues($value, ObjectManager $objectManager): mixed
    {
        if (is_array($value)) {
            if (isset($value['_entity_class'])) {
                $serializedId = sha1(serialize($value['_entity_id']));
                if (!isset($this->_references[$value['_entity_class']][$serializedId])) {
                    $this->_references[$value['_entity_class']][$serializedId] = $objectManager->getRepository($value['_entity_class'])->findOneBy($value['_entity_id']);
                }

                return $this->_references[$value['_entity_class']][$serializedId];
            } else {
                foreach ($value as $key => $value2) {
                    $value[$key] = $this->untransformEntityValues($value2, $objectManager);
                }
            }
        }

        return $value;
    }
}
