<?php

namespace Softspring\CmsBundle\Model;

abstract class ContentVersion implements ContentVersionInterface
{
    protected ?ContentInterface $content = null;

    protected ?string $layout = null;

    protected ?int $createdAt = null;

    protected ?array $data = null;

    protected ?array $compiledModules = null;

    protected ?array $compiled = null;

    public function getContent(): ?ContentInterface
    {
        return $this->content;
    }

    public function setContent(?ContentInterface $content): void
    {
        $this->content = $content;
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
        return $this->createdAt ? \DateTime::createFromFormat('U', $this->createdAt) : null;
    }

    public function autoSetCreatedAt()
    {
        $this->createdAt = time();
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
}
