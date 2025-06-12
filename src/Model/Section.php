<?php

namespace Softspring\CmsBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @property SectionVersionInterface[]|Collection          $versions
 * @property SectionVersionInterface|VersionInterface|null $publishedVersion
 * @property SectionVersionInterface|VersionInterface|null $lastVersion
 */
abstract class Section implements SectionInterface
{
    use Traits\VersionableTrait;

    protected ?string $name = null;

    public function __construct()
    {
        $this->versions = new ArrayCollection();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }
}
