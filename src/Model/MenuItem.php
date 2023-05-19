<?php

namespace Softspring\CmsBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

abstract class MenuItem implements MenuItemInterface
{
    protected ?MenuInterface $menu = null;

    protected ?int $type = MenuItemInterface::TYPE_ROUTE;

    protected ?array $text = null;

    protected ?array $options = null;

    protected ?array $symfonyRoute = null;

    protected ?MenuItemInterface $parent = null;

    /**
     * @psalm-var Collection|MenuItemInterface[]|null
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

    public function getText(): ?array
    {
        return $this->text;
    }

    public function setText(?array $text): void
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

    public function getSymfonyRoute(): ?array
    {
        return $this->symfonyRoute;
    }

    public function setSymfonyRoute(?array $symfonyRoute): void
    {
        $this->symfonyRoute = $symfonyRoute;
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
