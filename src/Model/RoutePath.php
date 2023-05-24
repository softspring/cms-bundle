<?php

namespace Softspring\CmsBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

abstract class RoutePath implements RoutePathInterface
{
    protected ?string $id = null;

    protected ?RouteInterface $route = null;

    protected ?Collection $sites = null;

    protected ?string $path = null;

    protected ?string $compiledPath = null;

    protected ?int $cacheTtl = null;

    protected ?string $locale = null;

    public function __construct()
    {
        $this->sites = new ArrayCollection();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getRoute(): ?RouteInterface
    {
        return $this->route;
    }

    public function setRoute(?RouteInterface $route): void
    {
        $this->route = $route;
        $this->compilePath();
        $this->getRoute()?->compileChildrenPaths();
    }

    public function getSites(): Collection
    {
        return $this->sites;
    }

    public function addSite(SiteInterface $site): void
    {
        if (!$this->getSites()->contains($site)) {
            $this->getSites()->add($site);
        }
    }

    public function removeSite(SiteInterface $site): void
    {
        if ($this->getSites()->contains($site)) {
            $this->getSites()->removeElement($site);
        }
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path): void
    {
        $this->path = $path;
        $this->compilePath();
        $this->getRoute()?->compileChildrenPaths();
    }

    public function getCompiledPath(): ?string
    {
        return $this->compiledPath;
    }

    public function setCompiledPath(?string $compiledPath): void
    {
        $this->compiledPath = $compiledPath;
    }

    public function getCacheTtl(): ?int
    {
        return $this->cacheTtl;
    }

    public function setCacheTtl(?int $cacheTtl): void
    {
        $this->cacheTtl = $cacheTtl;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(?string $locale): void
    {
        $this->locale = $locale;
        $this->compilePath();
        $this->getRoute()?->compileChildrenPaths();
    }

    public function compilePath(): void
    {
        if (!$this->getRoute()) {
            // not yet
            return;
        }

        if (RouteInterface::TYPE_PARENT_ROUTE == $this->getRoute()->getType()) {
            $this->compiledPath = null;

            $this->getRoute()->compileChildrenPaths();

            return;
        }

        $slugs = [];

        $parentRoute = $this->getRoute()->getParent();
        while ($parentRoute) {
            /** @var RoutePathInterface|false $parentRoutePath */
            $parentRoutePath = $parentRoute->getPaths()->filter(fn (RoutePathInterface $path) => $path->getLocale() == $this->getLocale())->first();
            if ($parentRoutePath) {
                $slugs[] = $parentRoutePath->getPath();
            }
            $parentRoute = $parentRoute->getParent();
        }

        $slugs = array_reverse($slugs);
        $slugs[] = $this->getPath();

        $this->setCompiledPath(trim(implode('/', $slugs), '/'));
    }
}
