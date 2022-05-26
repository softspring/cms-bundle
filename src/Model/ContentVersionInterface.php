<?php

namespace Softspring\CmsBundle\Model;

interface ContentVersionInterface
{
    public function getId(): ?string;

    public function getContent(): ?ContentInterface;

    public function setContent(?ContentInterface $content): void;

    public function getLayout(): ?string;

    public function setLayout(?string $layout): void;

    public function getData(): ?array;

    public function setData(?array $data): void;

    public function getCompiledModules(): ?array;

    public function setCompiledModules(?array $compiledModules): void;

    public function getCompiled(): ?array;

    public function setCompiled(?array $compiled): void;
}
