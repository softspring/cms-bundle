<?php

namespace Softspring\CmsBundle\Model;

use Doctrine\Common\Collections\Collection;

interface RouteInterface
{
    public const TYPE_UNKNOWN = 0;
    public const TYPE_CONTENT = 1;
    public const TYPE_REDIRECT_TO_ROUTE = 2;
    public const TYPE_REDIRECT_TO_URL = 3;
    public const TYPE_PARENT_ROUTE = 4;

    public function getId();

    public function setId(?string $id): void;

    public function getType(): ?int;

    public function setType(?int $type): void;

    /**
     * @psalm-return SiteInterface[]|Collection
     */
    public function getSites(): Collection;

    public function addSite(SiteInterface $site): void;

    public function removeSite(SiteInterface $site): void;

    public function hasSite(string $site): bool;

    public function getParent(): ?RouteInterface;

    public function setParent(?RouteInterface $parent): void;

    /**
     * @psalm-return RouteInterface[]|Collection|null
     */
    public function getChildren(): ?Collection;

    public function addChild(RouteInterface $child): void;

    public function removeChild(RouteInterface $child): void;

    /**
     * @psalm-return RoutePathInterface[]|Collection
     */
    public function getPaths(): Collection;

    public function addPath(RoutePathInterface $path): void;

    public function removePath(RoutePathInterface $path): void;

    public function getPathForLocale(string $locale): ?RoutePathInterface;

    public function getContent(): ?ContentInterface;

    public function setContent(?ContentInterface $content): void;

    public function getRedirectUrl(): ?string;

    public function setRedirectUrl(?string $redirectUrl): void;

    public function getSymfonyRoute(): ?array;

    public function setSymfonyRoute(?array $symfonyRoute): void;

    public function getRedirectType(): ?int;

    public function setRedirectType(?int $redirectType): void;

    public function getLinkAttrs(): string;

    public function setLinkAttrs(?string $linkAttrs): void;

    public function compilePaths(): void;

    public function compileChildrenPaths(): void;
}
