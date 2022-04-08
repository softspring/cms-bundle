<?php

namespace Softspring\CmsBundle\Model;

interface BlockInterface
{
    public function getName(): ?string;

    public function setName(?string $name): void;

    public function getType(): ?string;

    public function setType(?string $type): void;

    public function getData(): ?array;

    public function setData(?array $data): void;
}
