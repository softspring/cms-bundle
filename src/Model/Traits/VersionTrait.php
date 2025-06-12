<?php

namespace Softspring\CmsBundle\Model\Traits;

use DateTime;

trait VersionTrait
{
    protected ?int $origin = null;

    protected ?string $originDescription = null;

    protected ?int $versionNumber = null;

    protected ?string $note = null;

    protected bool $keep = false;

    protected ?int $createdAt = null;

    protected ?array $meta = null;

    public function getOrigin(): ?int
    {
        return $this->origin;
    }

    public function setOrigin(?int $origin): void
    {
        $this->origin = $origin;
    }

    public function getOriginDescription(): ?string
    {
        return $this->originDescription;
    }

    public function setOriginDescription(?string $originDescription): void
    {
        $this->originDescription = $originDescription;
    }

    public function getVersionNumber(): ?int
    {
        return $this->versionNumber;
    }

    public function setVersionNumber(?int $versionNumber): void
    {
        $this->versionNumber = $versionNumber;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): void
    {
        $this->note = $note;
    }

    public function isKeep(): bool
    {
        return $this->keep;
    }

    public function setKeep(bool $keep): void
    {
        $this->keep = $keep;
    }

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt ? DateTime::createFromFormat('U', "{$this->createdAt}") : null;
    }

    public function setCreatedAt(?DateTime $createdAt): void
    {
        $this->createdAt = $createdAt ? (int) $createdAt->format('U') : null;
    }

    public function autoSetCreatedAt(): void
    {
        if (!$this->createdAt) {
            $this->createdAt = time();
        }
    }

    public function getMeta(): ?array
    {
        return $this->meta;
    }

    public function setMeta(?array $meta): void
    {
        $this->meta = $meta;
    }

    public function setMetaField(string $field, mixed $value): void
    {
        $meta = $this->getMeta() ?? [];
        $meta[$field] = $value;
        $this->setMeta($meta);
    }

    public function getMetaField(string $field, mixed $default = null): mixed
    {
        return $this->getMeta()[$field] ?? $default;
    }

    public function isPublished(): bool
    {
        return $this->getParent()?->getPublishedVersion() === $this;
    }

    public function isLastVersion(): bool
    {
        return $this->getParent()?->getVersions()->first() === $this;
    }

    public function deleteOnCleanup(): bool
    {
        return !$this->isKeep() && !$this->isPublished() && !$this->isLastVersion();
    }
}
