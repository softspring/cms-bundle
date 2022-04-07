<?php

namespace Softspring\CmsBundle\Model;

interface BlockInterface
{
    public function getType(): ?string;

    public function setType(?string $type): void;

    public function getData(): ?array;

    public function setData(?array $data): void;
}
