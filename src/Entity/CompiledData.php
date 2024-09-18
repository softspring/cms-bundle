<?php

namespace Softspring\CmsBundle\Entity;

use Softspring\CmsBundle\Model\CompiledData as CompiledDataModel;

class CompiledData extends CompiledDataModel
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
