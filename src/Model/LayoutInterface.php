<?php

namespace Softspring\CmsBundle\Model;

use Doctrine\Common\Collections\Collection;

interface LayoutInterface
{
    public function getId(): ?string;

    public function getName(): ?string;

    public function setName(?string $name): void;

    public function getTemplate(): ?string;

    public function setTemplate(?string $template): void;

    /**
     * @return Collection|PageInterface[]
     */
    public function getPages(): Collection;

    /**
     * @param Collection|PageInterface[] $pages
     */
    public function setPages($pages): void;
}