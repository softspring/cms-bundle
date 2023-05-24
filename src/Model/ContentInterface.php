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

    public function getExtraData(): ?array;

    public function setExtraData(?array $extraData): void;

    public function getSeo(): ?array;

    public function setSeo(?array $seo): void;

    public function getPublishedVersion(): ?ContentVersionInterface;

    public function setPublishedVersion(?ContentVersionInterface $publishedVersion): void;
}
