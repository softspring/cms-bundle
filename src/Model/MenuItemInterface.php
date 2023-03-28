<?php

namespace Softspring\CmsBundle\Model;

use Doctrine\Common\Collections\Collection;

interface MenuItemInterface
{
    public const TYPE_UNKNOWN = 0;
    public const TYPE_ROUTE = 1;
    public const TYPE_SUBMENU = 2;

    public function getId();

    public function setMenu(?MenuInterface $menu): void;

    public function getMenu(): ?MenuInterface;

    public function setType(?int $type): void;

    public function getType(): ?int;

    public function setText(?array $text): void;

    public function getText(): ?array;

    public function getSymfonyRoute(): ?array;

    public function setSymfonyRoute(?array $symfonyRoute): void;

    public function getOptions(): ?array;

    public function setOptions(?array $options): void;

    public function getParent(): ?MenuItemInterface;

    public function setParent(?MenuItemInterface $parent): void;

    /**
     * @psalm-return Collection|MenuItemInterface[]|null
     */
    public function getItems(): ?Collection;

    public function addItem(MenuItemInterface $item): void;

    public function removeItem(MenuItemInterface $item): void;
}
