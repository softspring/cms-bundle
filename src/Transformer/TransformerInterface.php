<?php

namespace Softspring\CmsBundle\Transformer;

use Doctrine\ORM\EntityManagerInterface;

interface TransformerInterface
{
    /**
     * @throws UnsupportedException
     */
    public function transform(object $entity, EntityManagerInterface $em): void;

    /**
     * @throws UnsupportedException
     */
    public function untransform(object $entity, EntityManagerInterface $em): void;
}
