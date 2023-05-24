<?php

namespace Softspring\CmsBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

abstract class Content implements ContentInterface
{
    protected ?string $name = null;

    protected ?Collection $sites = null;

    /**
     * @psalm-var ContentVersionInterface[]|Collection
     */
    protected Collection $versions;

    protected ?int $lastVersionNumber = null;

    /**
     * @psalm-var RouteInterface[]|Collection
     */
    protected Collection $routes;

    protected ?RouteInterface $canonical;

    protected ?array $extraData = null;

    protected ?array $seo = null;

    protected ?ContentVersionInterface $publishedVersion = null;

    public function __construct()
    {
        $this->sites = new ArrayCollection();
        $this->versions = new ArrayCollection();
        $this->routes = new ArrayCollection();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getSites(): Collection
    {
        return $this->sites;
    }

    public function addSite(SiteInterface $site): void
    {
        if (!$this->getSites()->contains($site)) {
            $this->getSites()->add($site);

            foreach ($this->getRoutes() as $route) {
                $route->addSite($site);
            }
        }
    }

    public function removeSite(SiteInterface $site): void
    {
        if ($this->getSites()->contains($site)) {
            $this->getSites()->removeElement($site);

            foreach ($this->getRoutes() as $route) {
                $route->removeSite($site);
            }
        }
    }

    /**
     * @psalm-return Collection|ContentVersionInterface[]
     */
    public function getVersions(): Collection
    {
        return $this->versions;
    }

    public function addVersion(ContentVersionInterface $version): void
    {
        if (!$this->versions->contains($version)) {
            $this->versions->add($version);
            $version->setContent($this);
        }
    }

    public function removeVersion(ContentVersionInterface $version): void
    {
        if ($this->versions->contains($version)) {
            $this->versions->removeElement($version);
        }
    }

    public function getLastVersionNumber(): ?int
    {
        return $this->lastVersionNumber;
    }

    public function setLastVersionNumber(?int $lastVersionNumber): void
    {
        $this->lastVersionNumber = $lastVersionNumber;
    }

    /**
     * @psalm-return Collection|RouteInterface[]
     */
    public function getRoutes(): Collection
    {
        return $this->routes;
    }

    public function addRoute(RouteInterface $route): void
    {
        if (!$this->routes->contains($route)) {
            $this->routes->add($route);
            $route->setContent($this);
            // $route->setSite($this->getSite()); TODO FIX THIS
        }
    }

    public function removeRoute(RouteInterface $route): void
    {
        if ($this->routes->contains($route)) {
            $this->routes->removeElement($route);
        }
    }

    public function getExtraData(): ?array
    {
        return $this->extraData;
    }

    public function setExtraData(?array $extraData): void
    {
        $this->extraData = $extraData;
    }

    public function getSeo(): ?array
    {
        return $this->seo;
    }

    public function setSeo(?array $seo): void
    {
        $this->seo = $seo;
    }

    public function getPublishedVersion(): ?ContentVersionInterface
    {
        return $this->publishedVersion;
    }

    public function setPublishedVersion(?ContentVersionInterface $publishedVersion): void
    {
        $this->publishedVersion = $publishedVersion;
    }
}
