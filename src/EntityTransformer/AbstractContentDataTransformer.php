<?php

namespace Softspring\CmsBundle\EntityTransformer;

use Doctrine\Persistence\ObjectManager;

abstract class AbstractContentDataTransformer
{
    use TransformEntityValuesTrait;

    protected function transformModule(array $fields, array &$data, ObjectManager $em, array &$entities): void
    {
        foreach ($fields as $field => $value) {
            if ('modules' === $field) {
                $this->transformSubmodule($value, $data[$field], $em, $entities);
            } else {
                $data[$field] = $this->transformEntityValues($value, $em, $entities);
            }
        }
    }

    protected function transformSubmodule(array $submodules, array &$data, ObjectManager $em, array &$entities): void
    {
        foreach ($submodules as $submodule => $fields) {
            foreach ($fields as $field => $value) {
                $data[$submodule][$field] = $this->transformEntityValues($value, $em, $entities);
            }
        }
    }

    protected function untransformModule(array $fields, array &$data, ObjectManager $em): void
    {
        foreach ($fields as $field => $value) {
            if ('modules' === $field) {
                $this->untransformSubmodule($value, $data[$field], $em);
            } else {
                $data[$field] = $this->untransformEntityValues($value, $em);
            }
        }
    }

    protected function untransformSubmodule(array $submodules, array &$data, ObjectManager $em): void
    {
        foreach ($submodules as $submodule => $fields) {
            foreach ($fields as $field => $value) {
                $data[$submodule][$field] = $this->untransformEntityValues($value, $em);
            }
        }
    }
}
