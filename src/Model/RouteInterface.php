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

    /**
     * @param RoutePathInterface $path
     */
    public function addPath(RoutePathInterface $path): void;

    /**
     * @param RoutePathInterface $path
     */
    public function removePath(RoutePathInterface $path): void;

    /**
     * @return PageInterface|null
     */
    public function getPage(): ?PageInterface;

    /**
     * @param PageInterface|null $page
     */
    public function setPage(?PageInterface $page): void;
}