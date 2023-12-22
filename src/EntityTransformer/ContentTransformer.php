<?php

namespace Softspring\CmsBundle\EntityTransformer;

use Doctrine\Persistence\ObjectManager;
use Softspring\CmsBundle\Model\ContentInterface;

class ContentTransformer implements TransformerInterface
{
    use TransformEntityValuesTrait;

    public function transform(object $entity, ObjectManager $em): void
    {
        $content = $this->getContent($entity);

        if (!$content->getExtraData()) {
            return;
        }

        $extraData = $content->getExtraData();
        foreach ($extraData as $field => $value) {
            $extraData[$field] = $this->transformEntityValues($value, $em);
        }
        $content->setExtraData($extraData);
    }

    public function untransform(object $entity, ObjectManager $em): void
    {
        $content = $this->getContent($entity);

        if (!$content->getExtraData()) {
            return;
        }

        $extraData = $content->getExtraData();
        foreach ($extraData as $field => $value) {
            $extraData[$field] = $this->untransformEntityValues($value, $em);
        }
        $content->setExtraData($extraData);
    }

    protected function getContent($entity): ContentInterface
    {
        if (!$entity instanceof ContentInterface) {
            throw new UnsupportedException(ContentInterface::class, get_class($entity));
        }

        return $entity;
    }
}
