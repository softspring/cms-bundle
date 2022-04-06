<?php

namespace Softspring\CmsBundle\Model;

use Doctrine\Common\Collections\Collection;

interface RouteInterface
{
    const TYPE_UNKNOWN = 0;
    const TYPE_CONTENT = 1;
    const TYPE_REDIRECT_TO_ROUTE = 2;
    const TYPE_REDIRECT_TO_URL = 3;
    const TYPE_PARENT_ROUTE = 4;

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

    public function getContent(): ?ContentInterface;

    public function setContent(?ContentInterface $content): void;

    public function getRedirectUrl(): ?string;

    public function setRedirectUrl(?string $redirectUrl): void;

    public function getSymfonyRoute(): ?string;

    public function setSymfonyRoute(?string $symfonyRoute): void;

    public function getRedirectType(): ?int;

    public function setRedirectType(?int $redirectType): void;
}
