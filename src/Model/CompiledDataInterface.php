<?php

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

    public function getContentVersion(): ?ContentVersionInterface;

    public function setContentVersion(?ContentVersionInterface $contentVersion): void;
}
