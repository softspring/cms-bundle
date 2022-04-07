<?php

namespace Softspring\CmsBundle\Model;

abstract class Block implements BlockInterface
{
    protected ?string $type = null;

    protected ?array $data = null;

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function setData(?array $data): void
    {
        $this->data = $data;
    }
}
