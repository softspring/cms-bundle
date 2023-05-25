<?php

namespace Softspring\CmsBundle\Model;

class Site implements SiteInterface
{
    protected ?string $id;

    protected ?array $config = null;

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
}
