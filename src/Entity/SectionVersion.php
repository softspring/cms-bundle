<?php

namespace Softspring\CmsBundle\Entity;

use Softspring\CmsBundle\Model\SectionVersion as SectionVersionModel;

class SectionVersion extends SectionVersionModel
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
