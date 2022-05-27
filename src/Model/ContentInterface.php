<?php

namespace Softspring\CmsBundle\Model;

use Doctrine\Common\Collections\Collection;

interface ContentInterface
{
    public function getId(): ?string;

    public function getName(): ?string;

    public function setName(?string $name): void;

    /**
     * @return ContentVersionInterface[]|Collection
     */
    public function getVersions(): Collection;

    public function addVersion(ContentVersionInterface $version): void;

    public function removeVersion(ContentVersionInterface $version): void;

    /**
     * @return RouteInterface[]|Collection
     */
    public function getRoutes(): Collection;

    public function addRoute(RouteInterface $route): void;

    public function removeRoute(RouteInterface $route): void;

    public function getExtraData(): ?array;

    public function setExtraData(?array $data): void;

    public function getSeo(): ?array;

    public function setSeo(?array $seo): void;

    public function getPublishedVersion(): ?ContentVersionInterface;

    public function setPublishedVersion(?ContentVersionInterface $publishedVersion): void;
}
