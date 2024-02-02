<?php

namespace Softspring\CmsBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Softspring\CmsBundle\Model\MenuInterface;
use Softspring\Component\CrudlController\Manager\CrudlEntityManagerTrait;

class MenuManager implements MenuManagerInterface
{
    use CrudlEntityManagerTrait;

    protected EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function createEntity(?string $menuType = null): object
    {
        $class = $this->getEntityClass();
        /** @var MenuInterface $menu */
        $menu = new $class();
        $menu->setType($menuType);

        return $menu;
    }

    public function getTargetClass(): string
    {
        return MenuInterface::class;
    }
}
