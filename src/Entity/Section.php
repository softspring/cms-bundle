<?php

namespace Softspring\CmsBundle\Entity;

use Softspring\CmsBundle\Model\Section as SectionModel;

class Section extends SectionModel
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
