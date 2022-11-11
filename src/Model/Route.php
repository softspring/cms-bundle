<?php

namespace Softspring\CmsBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

abstract class Route implements RouteInterface
{
    protected ?string $id = null;
    protected ?int $type = null;
    protected ?string $site = null;

    /**
     * @var RoutePathInterface[]|Collection
     */
    protected Collection $paths;

    protected ?ContentInterface $content = null;
    protected ?string $redirectUrl = null;
    protected ?array $symfonyRoute = null;
    protected ?int $redirectType = null;

    public function __construct()
    {
        $this->paths = new ArrayCollection();
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

    public function getSite(): ?string
    {
        return $this->site;
    }

    public function setSite(?string $site): void
    {
        $this->site = $site;
    }

    /**
     * @return RoutePathInterface[]|Collection
     */
    public function getPaths()
    {
        return $this->paths;
    }

    public function addPath(RoutePathInterface $path): void
    {
        if (!$this->paths->contains($path)) {
            $this->paths->add($path);
            $path->setRoute($this);
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
}
