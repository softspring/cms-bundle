<?php

namespace Softspring\CmsBundle\Model;

abstract class Block implements BlockInterface
{
    protected ?string $key = null;

    protected ?array $content = null;

    public function getKey(): ?string
    {
        return $this->key;
    }

    public function setKey(?string $key): void
    {
        $this->key = $key;
    }

    public function getContent(): ?array
    {
        return $this->content;
    }

    public function setContent(?array $content): void
    {
        $this->content = $content;
    }
}
