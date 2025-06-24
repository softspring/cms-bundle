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
    use Traits\TranslatableConfigTrait;

    protected ?string $name = null;

    protected ?array $extraData = null;

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

    public function getExtraData(): array
    {
        return $this->extraData ?? [];
    }

    public function setExtraData(?array $extraData): void
    {
        $this->extraData = $extraData;
    }

    public function getExtra(string $key, mixed $default = null): mixed
    {
        return $this->extraData[$key] ?? $default;
    }

    public function setExtra(string $key, mixed $value): void
    {
        if (null === $this->extraData) {
            $this->extraData = [];
        }
        $this->extraData[$key] = $value;
    }
}
