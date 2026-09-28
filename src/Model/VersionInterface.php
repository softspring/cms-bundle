<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Model;

use DateTime;

interface VersionInterface
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

    public function setParent(?VersionableInterface $parent): void;

    public function getParent(): ?VersionableInterface;

    public function getOrigin(): ?int;

    public function setOrigin(?int $origin): void;

    public function getVersionNumber(): ?int;

    public function setVersionNumber(?int $versionNumber): void;

    public function isLastVersion(): bool;

    public function getOriginDescription(): ?string;

    public function setOriginDescription(?string $originDescription): void;

    public function getNote(): ?string;

    public function setNote(?string $note): void;

    public function isKeep(): bool;

    public function setKeep(bool $keep): void;

    public function getCreatedAt(): ?DateTime;

    public function setCreatedAt(?DateTime $createdAt): void;

    public function autoSetCreatedAt(): void;

    public function getMeta(): ?array;

    public function setMeta(?array $meta): void;

    public function setMetaField(string $field, mixed $value): void;

    public function getMetaField(string $field, mixed $default = null): mixed;

    public function isPublished(): bool;

    public function deleteOnCleanup(): bool;
}
