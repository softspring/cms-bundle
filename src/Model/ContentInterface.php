<?php

namespace Softspring\CmsBundle\Model;

use Doctrine\Common\Collections\Collection;

/**
 * @method ContentVersionInterface[]|Collection getVersions()
 * @method void                                 addVersion(ContentVersionInterface $version)
 * @method void                                 removeVersion(ContentVersionInterface $version)
 * @method ContentVersionInterface|null         getPublishedVersion()
 * @method void                                 setPublishedVersion(ContentVersionInterface|null $publishedVersion)
 * @method ContentVersionInterface|null         getLastVersion()
 * @method void                                 setLastVersion(?ContentVersionInterface $lastVersion)
 */
interface ContentInterface extends VersionableInterface, TranslatableConfigInterface
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
     * @return RouteInterface[]|Collection
     */
    public function getRoutes(): Collection;

    public function addRoute(RouteInterface $route): void;

    public function removeRoute(RouteInterface $route): void;

    public function getCanonicalRoutePath(?string $locale = null): ?RoutePathInterface;

    public function getExtraData(): ?array;

    public function setExtraData(?array $extraData): void;

    public function getIndexing(): ?array;

    public function setIndexing(?array $indexing): void;

    public function getDefaultLocale(): ?string;

    public function setDefaultLocale(?string $defaultLocale): void;

    public function getLocales(): ?array;

    public function setLocales(?array $locales): void;

    public function addLocale(string $locale): void;
}
