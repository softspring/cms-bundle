<?php

namespace Softspring\CmsBundle\Model;

interface BlockInterface
{
    public function getKey(): ?string;

    public function setKey(?string $key): void;

    public function getContent(): ?array;

    public function setContent(?array $content): void;
}
