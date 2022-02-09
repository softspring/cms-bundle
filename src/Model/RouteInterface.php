<?php

namespace Softspring\CmsBundle\Model;

use Doctrine\Common\Collections\Collection;

interface RouteInterface
{
    public function getId(): ?string;

    public function setId(?string $id): void;

    /**
     * @return RoutePathInterface[]|Collection
     */
    public function getPaths();

    public function addPath(RoutePathInterface $path): void;

    public function removePath(RoutePathInterface $path): void;

    public function getPage(): ?PageInterface;

    public function setPage(?PageInterface $page): void;
}
