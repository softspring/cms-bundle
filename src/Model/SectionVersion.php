<?php

namespace Softspring\CmsBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use InvalidArgumentException;

abstract class SectionVersion implements SectionVersionInterface
{
    use Traits\ContentDataTrait;
    use Traits\CompilableTrait;
    use Traits\VersionTrait;

    protected ?SectionInterface $section = null;

    public function __construct()
    {
        $this->medias = new ArrayCollection();
        $this->routes = new ArrayCollection();
        $this->sections = new ArrayCollection();
        $this->compiled = new ArrayCollection();
    }

    public function getSection(): ?SectionInterface
    {
        return $this->section;
    }

    public function setSection(?SectionInterface $section): void
    {
        $this->section = $section;
    }

    public function setParent(?VersionableInterface $parent): void
    {
        if ($parent && !$parent instanceof SectionInterface) {
            throw new InvalidArgumentException('Parent must implement SectionInterface');
        }
        $this->setSection($parent);
    }

    public function getParent(): ?VersionableInterface
    {
        return $this->getSection();
    }
}
