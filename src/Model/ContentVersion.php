<?php

namespace Softspring\CmsBundle\Model;

abstract class ContentVersion implements ContentVersionInterface
{
    protected ?ContentInterface $content = null;

    protected ?int $origin = null;

    protected ?string $layout = null;

    protected ?int $createdAt = null;

    protected ?int $versionNumber = null;

    protected ?array $data = null;

    protected ?array $compiledModules = null;

    protected ?array $compiled = null;

    protected bool $keep = false;

    public function getContent(): ?ContentInterface
    {
        return $this->content;
    }

    public function setContent(?ContentInterface $content): void
    {
        $this->content = $content;
    }

    public function getOrigin(): ?int
    {
        return $this->origin;
    }

    public function setOrigin(?int $origin): void
    {
        $this->origin = $origin;
    }

    public function getLayout(): ?string
    {
        return $this->layout;
    }

    public function setLayout(?string $layout): void
    {
        $this->layout = $layout;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt ? \DateTime::createFromFormat('U', "{$this->createdAt}") : null;
    }

    public function setCreatedAt(?\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt ? (int) $createdAt->format('U') : null;
    }

    public function autoSetCreatedAt()
    {
        if (!$this->createdAt) {
            $this->createdAt = time();
        }
    }

    public function getVersionNumber(): ?int
    {
        return $this->versionNumber;
    }

    public function setVersionNumber(?int $versionNumber): void
    {
        $this->versionNumber = $versionNumber;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function setData(?array $data): void
    {
        $this->data = $data;
    }

    public function getCompiledModules(): ?array
    {
        return $this->compiledModules;
    }

    public function setCompiledModules(?array $compiledModules): void
    {
        $this->compiledModules = $compiledModules;
    }

    public function getCompiled(): ?array
    {
        return $this->compiled;
    }

    public function setCompiled(?array $compiled): void
    {
        $this->compiled = $compiled;
    }

    public function isPublished(): bool
    {
        return $this->getContent()->getPublishedVersion() == $this;
    }

    public function isLastVersion(): bool
    {
        return $this->getContent()->getVersions()->first() == $this;
    }

    public function deleteOnCleanup(): bool
    {
        return !$this->isKeep() && !$this->isPublished() && !$this->isLastVersion();
    }

    public function isKeep(): bool
    {
        return $this->keep;
    }

    public function setKeep(bool $keep): void
    {
        $this->keep = $keep;
    }
}
