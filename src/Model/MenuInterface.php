<?php

namespace Softspring\CmsBundle\Model;

use Doctrine\Common\Collections\Collection;

interface MenuInterface
{
    public function getId();

    public function setType(string $type): void;

    public function getType(): ?string;

    public function setName(string $name): void;

    public function getName(): ?string;

    /**
     * @psalm-return Collection|MenuItemInterface[]|null
     */
    public function getItems(): ?Collection;

    public function addItem(MenuItemInterface $item): void;

    public function removeItem(MenuItemInterface $item): void;
}
