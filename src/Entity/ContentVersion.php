<?php

namespace Softspring\CmsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Softspring\CmsBundle\Model\ContentVersion as ContentVersionModel;

class ContentVersion extends ContentVersionModel
{
    protected ?string $id = null;

    public function __construct()
    {
        $this->medias = new ArrayCollection();
        $this->routes = new ArrayCollection();
        $this->compiled = new ArrayCollection();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function __toString(): string
    {
        return ''.$this->getId();
    }
}
