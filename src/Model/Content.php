<?php

namespace Softspring\CmsBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

abstract class Content implements ContentInterface
{
    protected ?string $name = null;

    /**
     * @var ContentVersionInterface[]|Collection
     */
    protected Collection $versions;

    /**
     * @var RouteInterface[]|Collection
     */
    protected Collection $routes;

    protected ?array $extraData = null;

    protected ?array $seo = null;

    public function __construct()
    {
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

    /**
     * @return Collection|ContentVersionInterface[]
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

    /**
     * @return Collection|RouteInterface[]
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
}
