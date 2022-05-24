<?php

namespace Softspring\CmsBundle\Entity;

use Softspring\CmsBundle\Model\ContentVersion as ContentVersionModel;

class ContentVersion extends ContentVersionModel
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