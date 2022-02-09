<?php

namespace Softspring\CmsBundle\Model;

abstract class RoutePath implements RoutePathInterface
{
    protected ?string $id;

    protected ?RouteInterface $route;

    protected ?string $path;

    protected ?int $cacheTtl;

    protected ?string $locale;

    public function getRoute(): ?RouteInterface
    {
        return $this->route;
    }

    public function setRoute(?RouteInterface $route): void
    {
        $this->route = $route;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path): void
    {
        $this->path = $path;
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
    }
}
