<?php

namespace Softspring\CmsBundle\EntityTransformer;

use Doctrine\Persistence\ObjectManager;
use Softspring\CmsBundle\Model\ContentDataInterface;
use Softspring\CmsBundle\Model\RouteInterface;
use Softspring\CmsBundle\Model\SectionInterface;
use Softspring\CmsBundle\Model\VersionInterface;
use Softspring\MediaBundle\Model\MediaInterface;

class VersionTransformer extends AbstractContentDataTransformer implements TransformerInterface
{
    public function transform(object $entity, ObjectManager $em): void
    {
        if (!$entity instanceof VersionInterface) {
            throw new UnsupportedException(VersionInterface::class, get_class($entity));
        }
        if (!$entity instanceof ContentDataInterface) {
            throw new UnsupportedException(ContentDataInterface::class, get_class($entity));
        }
        $version = $entity;

        $entities = [];
        $data = $version->getData() ?? [];
        $this->transformModule($data, $data, $em, $entities);
        $version->setData($data);

        // add media references
        foreach ($entities as $entity) {
            if ($entity instanceof MediaInterface) {
                $version->addMedia($entity);
            }
        }
        // add route references
        foreach ($entities as $entity) {
            if ($entity instanceof RouteInterface) {
                $version->addRoute($entity);
            }
        }
        // add route references
        foreach ($entities as $entity) {
            if ($entity instanceof SectionInterface) {
                $version->addSection($entity);
            }
        }
    }

    public function untransform(object $entity, ObjectManager $em): void
    {
        if (!$entity instanceof VersionInterface) {
            throw new UnsupportedException(VersionInterface::class, get_class($entity));
        }
        if (!$entity instanceof ContentDataInterface) {
            throw new UnsupportedException(ContentDataInterface::class, get_class($entity));
        }
        $version = $entity;

        if ($version->getData()) {
            $version->_setDataCallback(function ($data) use ($em) {
                $this->untransformModule($data, $data, $em);

                return $data;
            });
        }
    }
}
