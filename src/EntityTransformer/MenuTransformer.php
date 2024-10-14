<?php

namespace Softspring\CmsBundle\EntityTransformer;

use Doctrine\Persistence\ObjectManager;
use Softspring\CmsBundle\Model\MenuInterface;

class MenuTransformer implements TransformerInterface
{
    use TransformEntityValuesTrait;

    public function transform(object $entity, ObjectManager $em): void
    {
        $menu = $this->getMenu($entity);

        if (!$menu->getData()) {
            return;
        }

        $extraData = $menu->getData();
        foreach ($extraData as $field => $value) {
            $extraData[$field] = $this->transformEntityValues($value, $em);
        }
        $menu->setData($extraData);
    }

    public function untransform(object $entity, ObjectManager $em): void
    {
        $menu = $this->getMenu($entity);

        if (!$menu->getData()) {
            return;
        }

        $extraData = $menu->getData();
        foreach ($extraData as $field => $value) {
            $extraData[$field] = $this->untransformEntityValues($value, $em);
        }
        $menu->setData($extraData);
    }

    /**
     * @throws UnsupportedException
     */
    protected function getMenu($entity): MenuInterface
    {
        if (!$entity instanceof MenuInterface) {
            throw new UnsupportedException(MenuInterface::class, get_class($entity));
        }

        return $entity;
    }
}
