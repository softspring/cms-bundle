<?php

namespace Softspring\CmsBundle\Entity;

use Softspring\CmsBundle\Model\Block as BlockModel;

class Block extends BlockModel
{
    protected ?string $id;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function __toString(): string
    {
        return ''.$this->getId();
    }
}