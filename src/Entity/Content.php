<?php

namespace Softspring\CmsBundle\Entity;

use Softspring\CmsBundle\Model\Content as ContentModel;

abstract class Content extends ContentModel
{
    protected ?string $id = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function __toString(): string
    {
        return ''.$this->getId();
    }
}
