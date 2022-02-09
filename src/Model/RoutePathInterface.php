<?php

namespace Softspring\CmsBundle\Model;

interface RoutePathInterface
{
    public function getRoute(): ?RouteInterface;

    public function setRoute(?RouteInterface $route): void;

    public function getPath(): ?string;

    public function setPath(?string $path): void;

    public function getCacheTtl(): ?int;

    public function setCacheTtl(?int $cacheTtl): void;

    public function getLocale(): ?string;

    public function setLocale(?string $locale): void;
}
