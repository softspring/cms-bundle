<?php

namespace Softspring\CmsBundle\Model;

class Site implements SiteInterface
{
    protected ?string $id;

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

    protected ?array $config;

    public function getConfig(): ?array
    {
        return $this->config;
    }

    public function setConfig(?array $config): void
    {
        $this->config = $config;
    }
}
