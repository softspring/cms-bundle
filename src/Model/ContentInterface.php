<?php

namespace Softspring\CmsBundle\Model;

use Doctrine\Common\Collections\Collection;

interface ContentInterface
{
    public function getId();

    public function getName(): ?string;

    public function setName(?string $name): void;

    /**
     * @psalm-return SiteInterface[]|Collection
     */
    public function getSites(): Collection;

    public function addSite(SiteInterface $site): void;

    public function removeSite(SiteInterface $site): void;

    /**
     * @psalm-return ContentVersionInterface[]|Collection
     */
    public function getVersions(): Collection;

    public function addVersion(ContentVersionInterface $version): void;

    public function removeVersion(ContentVersionInterface $version): void;

    public function getLastVersionNumber(): ?int;

    public function setLastVersionNumber(?int $lastVersionNumber): void;

    /**
     * @return RouteInterface[]|Collection
     */
    public function getRoutes(): Collection;

    public function addRoute(RouteInterface $route): void;

    public function removeRoute(RouteInterface $route): void;

    public function getCanonicalRoutePath(?string $locale = null): ?RoutePathInterface;

    public function getExtraData(): ?array;

    public function setExtraData(?array $extraData): void;

    /**
     * @deprecated use getIndexing()
     */
    public function getSeo(): ?array;

    /**
     * @deprecated use setIndexing()
     */
    public function setSeo(?array $seo): void;

    public function getIndexing(): ?array;

    public function setIndexing(?array $indexing): void;

    public function getPublishedVersion(): ?ContentVersionInterface;

    public function setPublishedVersion(?ContentVersionInterface $publishedVersion): void;

    public function getStatus(): string;

    public function getLastVersion(): ?ContentVersionInterface;

    public function setLastVersion(?ContentVersionInterface $lastVersion): void;

    public function getDefaultLocale(): ?string;

    public function setDefaultLocale(?string $defaultLocale): void;

    public function getLocales(): ?array;

    public function setLocales(?array $locales): void;

    public function addLocale(string $locale): void;
}
