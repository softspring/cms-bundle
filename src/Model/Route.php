<?php

namespace Softspring\CmsBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

abstract class Route implements RouteInterface
{
    protected ?string $id = null;
    protected ?int $type = null;

    /**
     * @var RoutePathInterface[]|Collection
     */
    protected Collection $paths;

    protected ?PageInterface $page = null;

    public function __construct()
    {
        $this->paths = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getId();
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

    public function getPage(): ?PageInterface
    {
        return $this->page;
    }

    public function setPage(?PageInterface $page): void
    {
        $this->page = $page;
    }
}
