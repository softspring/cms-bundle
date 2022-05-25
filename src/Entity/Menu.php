<?php

namespace Softspring\CmsBundle\Entity;

use Softspring\CmsBundle\Model\Menu as MenuModel;

class Menu extends MenuModel
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
