<?php

namespace Softspring\CmsBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

abstract class Route implements RouteInterface
{
    protected ?string $id = null;
    protected ?int $type = null;
    protected ?Collection $sites = null;

    /**
     * @psalm-var RoutePathInterface[]|Collection
     */
    protected Collection $paths;

    /**
     * @psalm-var RouteInterface[]|Collection
     */
    protected Collection $children;

    protected ?RouteInterface $parent = null;
    protected ?ContentInterface $content = null;
    protected ?string $redirectUrl = null;
    protected ?array $symfonyRoute = null;
    protected ?int $redirectType = null;

    public function __construct()
    {
        $this->sites = new ArrayCollection();
        $this->paths = new ArrayCollection();
        $this->children = new ArrayCollection();
    }

    public function __toString()
    {
        return ''.$this->getId();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(?int $type): void
    {
        $this->type = $type;
    }

    public function getSites(): Collection
    {
        return $this->sites;
    }

    public function addSite(SiteInterface $site): void
    {
        if (!$this->getSites()->contains($site)) {
            $this->getSites()->add($site);

            foreach ($this->getPaths() as $path) {
                $path->addSite($site);
            }
        }
    }

    public function removeSite(SiteInterface $site): void
    {
        if ($this->getSites()->contains($site)) {
            $this->getSites()->removeElement($site);

            foreach ($this->getPaths() as $path) {
                $path->removeSite($site);
            }
        }
    }

    public function hasSite(string $site): bool
    {
        return (bool) $this->getSites()->filter(fn(SiteInterface $routeSite) => "$routeSite" === "$site")->count();
    }

    /**
     * @psalm-return RoutePathInterface[]|Collection
     */
    public function getPaths(): Collection
    {
        return $this->paths;
    }

    public function addPath(RoutePathInterface $path): void
    {
        if (!$this->paths->contains($path)) {
            $this->paths->add($path);
            $path->setRoute($this);

            foreach ($this->getSites() as $site) {
                $path->addSite($site);
            }
        }
    }

    public function removePath(RoutePathInterface $path): void
    {
        if ($this->paths->contains($path)) {
            $this->paths->removeElement($path);
            $path->setRoute(null);
        }
    }

    public function getContent(): ?ContentInterface
    {
        return $this->content;
    }

    public function setContent(?ContentInterface $content): void
    {
        $content && $this->setType(self::TYPE_CONTENT);
        $this->content = $content;
    }

    public function getRedirectUrl(): ?string
    {
        return $this->redirectUrl;
    }

    public function setRedirectUrl(?string $redirectUrl): void
    {
        $this->redirectUrl = $redirectUrl;
    }

    public function getSymfonyRoute(): ?array
    {
        return $this->symfonyRoute;
    }

    public function setSymfonyRoute(?array $symfonyRoute): void
    {
        $this->symfonyRoute = $symfonyRoute;
    }

    public function getRedirectType(): ?int
    {
        return $this->redirectType;
    }

    public function setRedirectType(?int $redirectType): void
    {
        $this->redirectType = $redirectType;
    }

    public function getParent(): ?RouteInterface
    {
        return $this->parent;
    }

    public function setParent(?RouteInterface $parent): void
    {
        $this->parent = $parent;
        $this->compilePaths();
    }

    /**
     * @return RouteInterface[]|Collection
     */
    public function getChildren(): ?Collection
    {
        return $this->children;
    }

    public function addChild(RouteInterface $child): void
    {
        if (!$this->children->contains($child)) {
            $this->children->add($child);
            $child->setParent($this);
        }
    }

    public function removeChild(RouteInterface $child): void
    {
        if ($this->children->contains($child)) {
            $this->children->removeElement($child);
            $child->setParent(null);
            $child->compilePaths();
        }
    }

    public function compilePaths(): void
    {
        foreach ($this->getPaths() as $path) {
            $path->compilePath();
        }
    }

    public function compileChildrenPaths(): void
    {
        foreach ($this->getChildren() as $child) {
            $child->compilePaths();
        }
    }
}
