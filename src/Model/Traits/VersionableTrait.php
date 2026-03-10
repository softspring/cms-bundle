<?php

namespace Softspring\CmsBundle\Model\Traits;

use DateTime;
use Doctrine\Common\Collections\Collection;
use Softspring\CmsBundle\Model\VersionInterface;

trait VersionableTrait
{
    /**
     * @psalm-var VersionInterface[]|Collection
     */
    protected Collection $versions;

    protected ?int $lastVersionNumber = null;

    protected ?VersionInterface $publishedVersion = null;

    protected ?VersionInterface $lastVersion = null;

    protected ?int $lastModified = null;

    /**
     * @psalm-return Collection|VersionInterface[]
     */
    public function getVersions(): Collection
    {
        return $this->versions;
    }

    public function addVersion(VersionInterface $version): void
    {
        if (!$this->versions->contains($version)) {
            $this->versions->add($version);
            $version->setParent($this);
        }
    }

    public function removeVersion(VersionInterface $version): void
    {
        if ($this->versions->contains($version)) {
            $this->versions->removeElement($version);
        }
    }

    public function getLastVersionNumber(): ?int
    {
        return $this->lastVersionNumber;
    }

    public function setLastVersionNumber(?int $lastVersionNumber): void
    {
        $this->lastVersionNumber = $lastVersionNumber;
    }

    public function getLastVersion(): ?VersionInterface
    {
        return $this->lastVersion;
    }

    public function setLastVersion(?VersionInterface $lastVersion): void
    {
        /* @phpstan-ignore-next-line */
        $this->lastVersion = $lastVersion;
    }

    public function getPublishedVersion(): ?VersionInterface
    {
        return $this->publishedVersion;
    }

    public function setPublishedVersion(?VersionInterface $publishedVersion): void
    {
        /* @phpstan-ignore-next-line */
        $this->publishedVersion = $publishedVersion;
        $this->setLastModified(new DateTime());
    }

    public function getLastModified(): ?DateTime
    {
        return $this->lastModified ? DateTime::createFromFormat('U', "{$this->lastModified}") : null;
    }

    public function setLastModified(?DateTime $lastModified): void
    {
        $this->lastModified = $lastModified instanceof DateTime ? (int) $lastModified->format('U') : null;
    }

    public function getStatus(): string
    {
        if ($this->getPublishedVersion()) {
            return 'published';
        }

        return 'draft';
    }
}
