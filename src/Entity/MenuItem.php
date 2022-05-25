<?php

namespace Softspring\CmsBundle\Entity;

use Softspring\CmsBundle\Model\MenuItem as MenuItemModel;

class MenuItem extends MenuItemModel
{
    protected ?string $id;

    public function getId(): ?string
    {
        return $this->id;
    }
}
