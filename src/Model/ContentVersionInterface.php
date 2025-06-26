<?php

namespace Softspring\CmsBundle\Model;

interface ContentVersionInterface extends VersionInterface, CompilableInterface, ContentDataInterface
{
    public function getId();

    public function getContent(): ?ContentInterface;

    public function setContent(?ContentInterface $content): void;

    public function getSeo(): ?array;

    public function setSeo(?array $seo): void;

    public function getLayout(): ?string;

    public function setLayout(?string $layout): void;
}
