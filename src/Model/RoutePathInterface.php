<?php

namespace Softspring\CmsBundle\Model;

use Doctrine\Common\Collections\Collection;

interface RoutePathInterface
{
    public const TYPE_REDIRECT_TO_CANONICAL = 1;

    public function getId();

    public function getRoute(): ?RouteInterface;

    public function setRoute(?RouteInterface $route): void;

    /**
     * @psalm-return SiteInterface[]|Collection
     */
    public function getSites(): Collection;

    public function addSite(SiteInterface $site): void;

    public function removeSite(SiteInterface $site): void;

    public function getPath(): ?string;

    public function setPath(?string $path): void;

    public function getCompiledPath(): ?string;

    public function setCompiledPath(?string $compiledPath): void;

    public function getCacheTtl(): ?int;

    public function setCacheTtl(?int $cacheTtl): void;

    public function getLocale(): ?string;

    public function setLocale(?string $locale): void;

    public function compilePath(): void;
}
