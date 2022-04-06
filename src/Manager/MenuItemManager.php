<?php

namespace Softspring\CmsBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Softspring\CmsBundle\Model\MenuItemInterface;
use Softspring\Component\CrudlController\Manager\CrudlEntityManagerTrait;

class MenuItemManager implements MenuItemManagerInterface
{
    use CrudlEntityManagerTrait;

    protected EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getTargetClass(): string
    {
        return MenuItemInterface::class;
    }
}
