<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\EntityTransformer;

use Doctrine\Persistence\ObjectManager;

interface TransformerInterface
{
    /**
     * @throws UnsupportedException
     */
    public function transform(object $entity, ObjectManager $em): void;

    /**
     * @throws UnsupportedException
     */
    public function untransform(object $entity, ObjectManager $em): void;
}
