<?php

namespace Softspring\CmsBundle\Model;

use Doctrine\Common\Collections\Collection;

/**
 * @method SectionVersionInterface[]|Collection getVersions()
 * @method void                                 addVersion(SectionVersionInterface $version)
 * @method void                                 removeVersion(SectionVersionInterface $version)
 * @method SectionVersionInterface|null         getPublishedVersion()
 * @method void                                 setPublishedVersion(SectionVersionInterface|null $publishedVersion)
 * @method SectionVersionInterface|null         getLastVersion()
 * @method void                                 setLastVersion(?SectionVersionInterface $lastVersion)
 */
interface SectionInterface extends VersionableInterface, TranslatableConfigInterface
{
    public function getId();

    public function getName(): ?string;

    public function setName(?string $name): void;

    public function getExtraData(): array;

    public function setExtraData(?array $extraData): void;

    public function getExtra(string $key, mixed $default = null): mixed;

    public function setExtra(string $key, mixed $value): void;
}
