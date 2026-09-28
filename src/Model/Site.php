<?php

namespace Softspring\CmsBundle\Model;

class Site implements SiteInterface
{
    protected ?string $id;

    protected ?array $config = null;

    protected ?array $metadata = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    public function __toString(): string
    {
        return "{$this->getId()}";
    }

    public function getConfig(): ?array
    {
        return $this->config;
    }

    public function setConfig(?array $config): void
    {
        $this->config = $config;
    }

    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    public function setMetadata(?array $metadata): void
    {
        $this->metadata = $metadata;
    }

    public function setMetadataField(string $field, mixed $value): void
    {
        $metadata = $this->getMetadata() ?? [];
        $metadata[$field] = $value;
        $this->setMetadata($metadata);
    }

    public function getMetadataField(string $field, mixed $default = null): mixed
    {
        return $this->getMetadata()[$field] ?? $default;
    }

    public function removeMetadataField(string $field): void
    {
        $metadata = $this->getMetadata() ?? [];
        unset($metadata[$field]);
        $this->setMetadata([] !== $metadata ? $metadata : null);
    }

    public function getCanonicalHost(): ?string
    {
        foreach ($this->getConfig()['hosts'] as $hostConfig) {
            if ($hostConfig['canonical']) {
                return $hostConfig['domain'];
            }
        }

        // return first host as default
        return $this->getConfig()['hosts'][0]['domain'] ?? null;
    }

    public function getCanonicalPort(): ?int
    {
        foreach ($this->getConfig()['hosts'] as $hostConfig) {
            if ($hostConfig['canonical']) {
                return $hostConfig['port'] ?? null;
            }
        }

        // return first host as default
        return $this->getConfig()['hosts'][0]['port'] ?? null;
    }

    public function getCanonicalScheme(): ?string
    {
        foreach ($this->getConfig()['hosts'] as $hostConfig) {
            if ($hostConfig['canonical'] && $hostConfig['scheme']) {
                return $hostConfig['scheme'];
            }
        }

        // return first host as default
        return $this->getConfig()['hosts'][0]['scheme'] ?? null;
    }

    public function getGeoHrefLangForLocale(string $locale): string
    {
        $extraConfig = $this->getConfig()['extra'] ?? [];

        return $extraConfig['hreflang_mapping'][$locale] ?? $locale;
    }
}
