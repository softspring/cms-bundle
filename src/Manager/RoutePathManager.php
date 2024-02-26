<?php

namespace Softspring\CmsBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Softspring\CmsBundle\Model\RoutePathInterface;
use Softspring\Component\CrudlController\Manager\CrudlEntityManagerTrait;

class RoutePathManager implements RoutePathManagerInterface
{
    use CrudlEntityManagerTrait;

    protected EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getTargetClass(): string
    {
        return RoutePathInterface::class;
    }

    public function duplicateEntity(RoutePathInterface $path, string $suffix = ''): RoutePathInterface
    {
        /** @var RoutePathInterface $newPath */
        $newPath = $this->createEntity();
        $newPath->setPath($path->getPath().($suffix ? '-'.$suffix : ''));
        $newPath->setLocale($path->getLocale());
        $newPath->setCacheTtl($path->getCacheTtl());

        return $newPath;
    }
}
