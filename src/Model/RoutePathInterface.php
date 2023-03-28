<?php

namespace Softspring\CmsBundle\Model;

interface RoutePathInterface
{
    public const TYPE_REDIRECT_TO_CANONICAL = 1;

    public function getId();

    public function getRoute(): ?RouteInterface;

    public function setRoute(?RouteInterface $route): void;

    public function getSite(): ?string;

    public function setSite(?string $site): void;

    public function getPath(): ?string;

    public function setPath(?string $path): void;

    public function getCompiledPath(): ?string;

    public function setCompiledPath(?string $compiledPath): void;

    public function getCacheTtl(): ?int;

    public function setCacheTtl(?int $cacheTtl): void;

    public function getLocale(): ?string;

    public function setLocale(?string $locale): void;

    public function compilePath(): void;
}
