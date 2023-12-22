<?php

namespace Softspring\CmsBundle\EntityTransformer;

use Doctrine\Persistence\ObjectManager;
use Softspring\CmsBundle\Model\BlockInterface;

class BlockTransformer implements TransformerInterface
{
    use TransformEntityValuesTrait;

    public function transform(object $entity, ObjectManager $em): void
    {
        $block = $this->getBlock($entity);

        if (!$block->getData()) {
            return;
        }

        $extraData = $block->getData();
        foreach ($extraData as $field => $value) {
            $extraData[$field] = $this->transformEntityValues($value, $em);
        }
        $block->setData($extraData);
    }

    public function untransform(object $entity, ObjectManager $em): void
    {
        $block = $this->getBlock($entity);

        if (!$block->getData()) {
            return;
        }

        $extraData = $block->getData();
        foreach ($extraData as $field => $value) {
            $extraData[$field] = $this->untransformEntityValues($value, $em);
        }
        $block->setData($extraData);
    }

    /**
     * @throws UnsupportedException
     */
    protected function getBlock($entity): BlockInterface
    {
        if (!$entity instanceof BlockInterface) {
            throw new UnsupportedException(BlockInterface::class, get_class($entity));
        }

        return $entity;
    }
}
