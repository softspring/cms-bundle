<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Model;

use DateTime;

interface CompiledDataInterface
{
    public function getKey(): ?string;

    public function setKey(?string $key): void;

    public function getExpiresAt(): ?DateTime;

    public function setExpiresAt(?DateTime $expiresAt): void;

    public function getData(): ?array;

    public function setData(?array $data): void;

    public function getDataPart(string $part): mixed;

    public function setDataPart(string $part, mixed $value): void;

    public function getVersion(): ?VersionInterface;

    public function setVersion(?VersionInterface $version): void;

    public function hasErrors(): bool;

    public function setErrors(bool $errors): void;
}
