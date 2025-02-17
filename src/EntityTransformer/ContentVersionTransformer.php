<?php

namespace Softspring\CmsBundle\EntityTransformer;

use Doctrine\Persistence\ObjectManager;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Model\ContentVersion;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\Model\RouteInterface;
use Softspring\CmsBundle\Utils\DataMigrator;
use Softspring\MediaBundle\Model\MediaInterface;

class ContentVersionTransformer implements TransformerInterface
{
    use TransformEntityValuesTrait;

    public function __construct(protected CmsConfig $cmsConfig)
    {
    }

    public function transform(object $entity, ObjectManager $em): void
    {
        $contentVersion = $this->getContentVersion($entity);

        $entities = [];
        $data = $contentVersion->getData();
        foreach ($data ?? [] as $layout => $modules) {
            $this->transformLayout($layout, $modules, $data, $em, $entities);
        }
        $contentVersion->setData($data);

        $seo = $contentVersion->getSeo();
        foreach ($seo ?? [] as $field => $value) {
            $seo[$field] = $this->transformEntityValues($value, $em, $entities);
        }
        $contentVersion->setSeo($seo);

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

    protected function transformLayout(string $layout, array $modules, array &$data, ObjectManager $em, array &$entities): void
    {
        foreach ($modules as $module => $fields) {
            $this->transformModule($fields, $data[$layout][$module], $em, $entities);
        }
    }

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

    public function untransform(object $entity, ObjectManager $em): void
    {
        /** @var ContentVersion $contentVersion */
        $contentVersion = $this->getContentVersion($entity);

        if ($contentVersion->getData()) {
            $contentVersion->_setDataCallback(function ($data) use ($em) {
                foreach ($data as $layout => $modules) {
                    $this->untransformLayout($layout, $modules, $data, $em);
                }

                return $data;
            });
        }

        if ($contentVersion->getSeo()) {
            $seo = $contentVersion->getSeo();

            $config = $this->cmsConfig->getContent($contentVersion->getContent());
            $seo = DataMigrator::migrate($config['seo_revision_migration_scripts'], $seo, $config['revision'], $this->cmsConfig);
            foreach ($seo as $field => $value) {
                $seo[$field] = $this->untransformEntityValues($value, $em);
            }
            $contentVersion->setSeo($seo);
        }
    }

    protected function untransformLayout(string $layout, array $modules, array &$data, ObjectManager $em): void
    {
        foreach ($modules as $module => $fields) {
            $this->untransformModule($fields, $data[$layout][$module], $em);
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

    /**
     * @throws UnsupportedException
     */
    protected function getContentVersion($entity): ContentVersionInterface
    {
        if (!$entity instanceof ContentVersionInterface) {
            throw new UnsupportedException(ContentVersionInterface::class, get_class($entity));
        }

        return $entity;
    }
}
