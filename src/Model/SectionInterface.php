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
interface SectionInterface extends VersionableInterface
{
    public function getId();

    public function getName(): ?string;

    public function setName(?string $name): void;
}
