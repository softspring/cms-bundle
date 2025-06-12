<?php

namespace Softspring\CmsBundle\Model;

interface ContentVersionInterface extends VersionInterface, CompilableInterface, ContentDataInterface
{
    public const ORIGIN_UNKNOWN = null;
    public const ORIGIN_EDIT = 1;
    public const ORIGIN_FIXTURE = 2;
    public const ORIGIN_IMPORT = 3;
    public const ORIGIN_TRANSLATIONS = 4;
    public const ORIGIN_SEO = 5;
    public const ORIGIN_DUPLICATE = 6;
    public const ORIGIN_ADD_LOCALE = 7;
    public const ORIGIN_ADD_SITE = 8;

    public function getId();

    public function getContent(): ?ContentInterface;

    public function setContent(?ContentInterface $content): void;

    public function getSeo(): ?array;

    public function setSeo(?array $seo): void;

    public function getLayout(): ?string;

    public function setLayout(?string $layout): void;
}
