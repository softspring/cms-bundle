<?php

namespace Softspring\CmsBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Softspring\CmsBundle\Utils\SitesSorter;

/**
 * @property ContentVersionInterface[]|Collection $versions
 * @property ContentVersionInterface|null         $publishedVersion
 * @property ContentVersionInterface|null         $lastVersion
 */
abstract class Content implements ContentInterface
{
    use Traits\VersionableTrait;
    use Traits\TranslatableConfigTrait;

    protected ?string $name = null;

    protected ?Collection $sites = null;

    /**
     * @psalm-var RouteInterface[]|Collection
     */
    protected Collection $routes;

    protected ?RouteInterface $canonical;

    protected ?array $extraData = null;

    protected ?array $indexing = null;

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

    public function getSitesSorted(): Collection
    {
        return SitesSorter::sort($this->getSites()->toArray());
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

    public function getCanonicalRoutePath(?string $locale = null): ?RoutePathInterface
    {
        // TODO, by now there is not a canonical mark in route paths, so we return the first one
        foreach ($this->getRoutes() as $route) {
            foreach ($route->getPaths() as $path) {
                if (!$locale) {
                    return $path;
                }

                if ($path->getLocale() === $locale) {
                    return $path;
                }
            }
        }

        return null;
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
        trigger_deprecation('sfs/cms-bundle', '5.2', 'Method %s is deprecated, use %s instead, and version.getSeo', __METHOD__, 'getIndexing');

        if (null === $this->getIndexing()) {
            return null;
        }

        return $this->getIndexing() + ($this->publishedVersion?->getSeo() ?: []);
    }

    public function setSeo(?array $seo): void
    {
        trigger_deprecation('sfs/cms-bundle', '5.2', 'Method %s is deprecated, use %s instead', __METHOD__, 'setIndexing');
        $this->setIndexing($seo);
    }

    public function getIndexing(): ?array
    {
        return $this->indexing;
    }

    public function setIndexing(?array $indexing): void
    {
        $this->indexing = $indexing;
    }
}
