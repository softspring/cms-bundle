<?php

namespace Softspring\CmsBundle\Model;

interface SiteInterface
{
    public function getId(): ?string;

    public function setId(?string $id): void;

    public function __toString(): string;

    public function setConfig(?array $config): void;

    public function getConfig(): ?array;

    public function getCanonicalHost(): ?string;

    public function getCanonicalScheme(): ?string;

    public function getGeoHrefLangForLocale(string $locale): string;
}
