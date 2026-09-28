<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Model;

use DateTime;
use Doctrine\Common\Collections\Collection;

interface VersionableInterface
{
    /**
     * @psalm-return VersionInterface[]|Collection
     */
    public function getVersions(): Collection;

    public function addVersion(VersionInterface $version): void;

    public function removeVersion(VersionInterface $version): void;

    public function getLastVersionNumber(): ?int;

    public function setLastVersionNumber(?int $lastVersionNumber): void;

    public function getPublishedVersion(): ?VersionInterface;

    public function setPublishedVersion(?VersionInterface $publishedVersion): void;

    public function getLastVersion(): ?VersionInterface;

    public function setLastVersion(?VersionInterface $lastVersion): void;

    public function getLastModified(): ?DateTime;

    public function setLastModified(?DateTime $lastModified): void;

    public function getStatus(): string;
}
