<?php

namespace Softspring\CmsBundle\Transformer;

use Doctrine\ORM\EntityManagerInterface;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\Model\RouteInterface;
use Softspring\MediaBundle\Model\MediaInterface;

class ContentVersionTransformer implements TransformerInterface
{
    use TransformEntityValuesTrait;

    public function transform(object $entity, EntityManagerInterface $em): void
    {
        $contentVersion = $this->getContentVersion($entity);

        if (!$contentVersion->getData()) {
            return;
        }

        $entities = [];
        $data = $contentVersion->getData();
        foreach ($data as $layout => $modules) {
            $this->transformLayout($layout, $modules, $data, $em, $entities);
        }
        $contentVersion->setData($data);

        // add media references
        foreach ($entities as $entity) {
            if ($entity instanceof MediaInterface) {
                $contentVersion->addMedia($entity);
            }
        }
        // add route references
        foreach ($entities as $entity) {
            if ($entity instanceof RouteInterface) {
                $contentVersion->addRoute($entity);
            }
        }
    }

    protected function transformLayout(string $layout, array $modules, array &$data, EntityManagerInterface $em, array &$entities): void
    {
        foreach ($modules as $module => $fields) {
            $this->transformModule($fields, $data[$layout][$module], $em, $entities);
        }
    }

    protected function transformModule(array $fields, array &$data, EntityManagerInterface $em, array &$entities): void
    {
        foreach ($fields as $field => $value) {
            if ('modules' === $field) {
                $this->transformSubmodule($value, $data[$field], $em, $entities);
            } else {
                $data[$field] = $this->transformEntityValues($value, $em, $entities);
            }
        }
    }

    protected function transformSubmodule(array $submodules, array &$data, EntityManagerInterface $em, array &$entities): void
    {
        foreach ($submodules as $submodule => $fields) {
            foreach ($fields as $field => $value) {
                $data[$submodule][$field] = $this->transformEntityValues($value, $em, $entities);
            }
        }
    }

    public function untransform(object $entity, EntityManagerInterface $em): void
    {
        $contentVersion = $this->getContentVersion($entity);

        if (!$contentVersion->getData()) {
            return;
        }

        $data = $contentVersion->getData();
        foreach ($data as $layout => $modules) {
            $this->untransformLayout($layout, $modules, $data, $em);
        }
        $contentVersion->setData($data);
    }

    protected function untransformLayout(string $layout, array $modules, array &$data, EntityManagerInterface $em): void
    {
        foreach ($modules as $module => $fields) {
            $this->untransformModule($fields, $data[$layout][$module], $em);
        }
    }

    protected function untransformModule(array $fields, array &$data, EntityManagerInterface $em): void
    {
        foreach ($fields as $field => $value) {
            if ('modules' === $field) {
                $this->untransformSubmodule($value, $data[$field], $em);
            } else {
                $data[$field] = $this->untransformEntityValues($value, $em);
            }
        }
    }

    protected function untransformSubmodule(array $submodules, array &$data, EntityManagerInterface $em): void
    {
        foreach ($submodules as $submodule => $fields) {
            foreach ($fields as $field => $value) {
                $data[$submodule][$field] = $this->untransformEntityValues($value, $em);
            }
        }
    }

    protected function getContentVersion($entity): ContentVersionInterface
    {
        if (!$entity instanceof ContentVersionInterface) {
            throw new UnsupportedException(ContentVersionInterface::class, get_class($entity));
        }

        return $entity;
    }
}
