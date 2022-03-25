<?php

namespace Softspring\CmsBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CrudlBundle\Manager\CrudlEntityManagerTrait;

class ContentVersionManager implements ContentVersionManagerInterface
{
    use CrudlEntityManagerTrait;

    protected EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getTargetClass(): string
    {
        return ContentVersionInterface::class;
    }
}
