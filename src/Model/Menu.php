<?php

namespace Softspring\CmsBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Menu implements MenuInterface
{
    protected ?string $id = null;

    protected ?string $name = null;

    protected ?string $type = null;

    /** @psalm-var Collection|MenuItemInterface[]|null */
    protected ?Collection $items = null;

    protected ?array $data = null;

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function __toString(): string
    {
        return ''.$this->getId();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    public function getItems(): ?Collection
    {
        return $this->items;
    }

    public function addItem(MenuItemInterface $item): void
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setMenu($this);
        }
    }

    public function removeItem(MenuItemInterface $item): void
    {
        if ($this->items->contains($item)) {
            $this->items->removeElement($item);
            $item->setMenu(null);
        }
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function setData(?array $data): void
    {
        $this->data = $data;
    }
}
