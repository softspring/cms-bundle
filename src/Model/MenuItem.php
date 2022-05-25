<?php

namespace Softspring\CmsBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

abstract class MenuItem implements MenuItemInterface
{
    protected ?MenuInterface $menu = null;

    protected ?int $type = MenuItemInterface::TYPE_ROUTE;

    protected ?string $text = null;

    protected ?array $options = null;

    protected ?RouteInterface $route = null;

    protected ?MenuItemInterface $parent = null;

    /**
     * @var Collection|MenuItemInterface[]|null
     */
    protected ?Collection $items = null;

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    public function getMenu(): ?MenuInterface
    {
        return $this->menu;
    }

    public function setMenu(?MenuInterface $menu): void
    {
        $this->menu = $menu;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(?int $type): void
    {
        $this->type = $type;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): void
    {
        $this->text = $text;
    }

    public function getOptions(): ?array
    {
        return $this->options;
    }

    public function setOptions(?array $options): void
    {
        $this->options = $options;
    }

    public function getRoute(): ?RouteInterface
    {
        return $this->route;
    }

    public function setRoute(?RouteInterface $route): void
    {
        $this->route = $route;
    }

    public function getParent(): ?MenuItemInterface
    {
        return $this->parent;
    }

    public function setParent(?MenuItemInterface $parent): void
    {
        $this->parent = $parent;
    }

    public function getItems(): ?Collection
    {
        return $this->items;
    }

    public function addItem(MenuItemInterface $item): void
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setParent($this);
        }
    }

    public function removeItem(MenuItemInterface $item): void
    {
        if ($this->items->contains($item)) {
            $this->items->removeElement($item);
            $item->setParent(null);
        }
    }
}
