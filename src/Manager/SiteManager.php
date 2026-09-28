<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Softspring\CmsBundle\Model\SiteInterface;
use Softspring\Component\CrudlController\Manager\CrudlEntityManagerTrait;

class SiteManager implements SiteManagerInterface
{
    use CrudlEntityManagerTrait;

    protected EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getTargetClass(): string
    {
        return SiteInterface::class;
    }
}
