<?php

namespace Softspring\CmsBundle\Model;

use Doctrine\Common\Collections\Collection;

interface MenuInterface
{
    public function setName(string $name): void;

    public function getName(): ?string;
    
    public function setTemplate(string $template): void;

    public function getTemplate(): ?string;

    /**
     * @return Collection|MenuItemInterface[]
     */
    public function getItems(): Collection;

    public function addItem(MenuItemInterface $item): void;

    public function removeItem(MenuItemInterface $item): void;
}