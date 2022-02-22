<?php

namespace Softspring\CmsBundle\Model;

use Doctrine\Common\Collections\Collection;

interface RouteInterface
{
    const TYPE_PAGE = 1;
    const TYPE_CONTENT = 2;
    const TYPE_STATIC = 3;
    const TYPE_NOT_FOUND = 4;
    const TYPE_REDIRECT_TO_ROUTE = 5;
    const TYPE_REDIRECT_TO_URL = 6;
    const TYPE_PARENT_ROUTE = 7;

    public function getId(): ?string;

    public function setId(?string $id): void;

    public function getType(): ?int;

    public function setType(?int $type): void;

    /**
     * @return RoutePathInterface[]|Collection
     */
    public function getPaths();

    public function addPath(RoutePathInterface $path): void;

    public function removePath(RoutePathInterface $path): void;

    public function getPage(): ?PageInterface;

    public function setPage(?PageInterface $page): void;
}
