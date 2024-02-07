<?php

namespace Softspring\CmsBundle\Admin\Menu;

class MenuItem
{
    public function __construct(
        protected string $id,
        protected string $text,
        protected ?string $url = null,
        protected bool $active = false,
        protected bool $disabled = true,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function isDisabled(): bool
    {
        return $this->disabled;
    }
}
