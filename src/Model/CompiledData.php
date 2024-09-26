<?php

namespace Softspring\CmsBundle\Model;

use DateTime;

class CompiledData implements CompiledDataInterface
{
    protected ?string $key = null;

    protected ?int $createdAt = null;

    protected ?int $expiresAt = null;

    protected ?array $data = null;

    protected ?ContentVersionInterface $contentVersion;

    protected bool $errors = false;

    public function getKey(): ?string
    {
        return $this->key;
    }

    public function setKey(?string $key): void
    {
        $this->key = $key;
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

    public function getExpiresAt(): ?DateTime
    {
        return $this->expiresAt ? DateTime::createFromFormat('U', "{$this->expiresAt}") : null;
    }

    public function setExpiresAt(?DateTime $expiresAt): void
    {
        $this->expiresAt = $expiresAt ? (int) $expiresAt->format('U') : null;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function setData(?array $data): void
    {
        $this->data = $data;
    }

    public function getDataPart(string $part): mixed
    {
        return $this->data[$part] ?? null;
    }

    public function setDataPart(string $part, mixed $value): void
    {
        $this->data[$part] = $value;
    }

    public function getContentVersion(): ?ContentVersionInterface
    {
        return $this->contentVersion;
    }

    public function setContentVersion(?ContentVersionInterface $contentVersion): void
    {
        $this->contentVersion = $contentVersion;
    }

    public function hasErrors(): bool
    {
        return $this->errors;
    }

    public function setErrors(bool $errors): void
    {
        $this->errors = $errors;

        if ($this->getContentVersion()) {
            $this->getContentVersion()->setCompileErrors($errors);
        }
    }
}
