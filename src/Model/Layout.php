<?php

namespace Softspring\CmsBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

abstract class Layout implements LayoutInterface
{
    protected ?string $name;

    protected ?string $template;

    /**
     * @var PageInterface[]|Collection
     */
    protected Collection $pages;

    public function __construct()
    {
        $this->pages = new ArrayCollection();
    }

    public function __toString(): string
    {
        return "{$this->getId()}";
    }

    abstract public function getId(): ?string;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function setTemplate(?string $template): void
    {
        $this->template = $template;
    }

    /**
     * @return Collection|PageInterface[]
     */
    public function getPages(): Collection
    {
        return $this->pages;
    }

    /**
     * @param Collection|PageInterface[] $pages
     */
    public function setPages($pages): void
    {
        $this->pages = $pages;
    }
}
