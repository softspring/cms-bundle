<?php

namespace Softspring\CmsBundle\Model;

use Doctrine\Common\Collections\Collection;

interface MenuItemInterface
{
    const TYPE_UNKNOWN = 0;
    const TYPE_ROUTE = 1;
    const TYPE_SUBMENU = 2;

    public function setMenu(?MenuInterface $menu): void;

    public function getMenu(): ?MenuInterface;

    public function setType(?int $type): void;

    public function getType(): ?int;

    public function setText(?string $text): void;

    public function getText(): ?string;

    public function setRoute(?RouteInterface $route): void;

    public function getRoute(): ?RouteInterface;

    public function getOptions(): ?array;

    public function setOptions(?array $options): void;

    public function getParent(): ?MenuItemInterface;

    public function setParent(?MenuItemInterface $parent): void;

    /**
     * @return Collection|null|MenuItemInterface[]
     */
    public function getItems(): ?Collection;

    public function addItem(MenuItemInterface $item): void;

    public function removeItem(MenuItemInterface $item): void;
}