<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Model;

interface SiteInterface
{
    public function getId(): ?string;

    public function setId(?string $id): void;

    public function __toString(): string;

    public function setConfig(?array $config): void;

    public function getConfig(): ?array;

    public function setMetadata(?array $metadata): void;

    public function getMetadata(): ?array;

    public function setMetadataField(string $field, mixed $value): void;

    public function getMetadataField(string $field, mixed $default = null): mixed;

    public function removeMetadataField(string $field): void;

    public function getCanonicalHost(): ?string;

    public function getCanonicalPort(): ?int;

    public function getCanonicalScheme(): ?string;

    public function getGeoHrefLangForLocale(string $locale): string;
}
