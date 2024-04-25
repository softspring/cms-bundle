<?php

namespace Softspring\CmsBundle\Model;

use DateTime;
use Doctrine\Common\Collections\Collection;
use Softspring\MediaBundle\Model\MediaInterface;

interface ContentVersionInterface
{
    public const ORIGIN_UNKNOWN = null;
    public const ORIGIN_EDIT = 1;
    public const ORIGIN_FIXTURE = 2;
    public const ORIGIN_IMPORT = 3;
    public const ORIGIN_TRANSLATIONS = 4;
    public const ORIGIN_SEO = 5;
    public const ORIGIN_DUPLICATE = 6;

    public function getId();

    public function getContent(): ?ContentInterface;

    public function setContent(?ContentInterface $content): void;

    public function getOrigin(): ?int;

    public function setOrigin(?int $origin): void;

    public function getSeo(): ?array;

    public function setSeo(?array $seo): void;

    public function getOriginDescription(): ?string;

    public function setOriginDescription(?string $originDescription): void;

    public function getNote(): ?string;

    public function setNote(?string $note): void;

    public function getLayout(): ?string;

    public function setLayout(?string $layout): void;

    public function getCreatedAt(): ?DateTime;

    public function setCreatedAt(?DateTime $createdAt): void;

    public function autoSetCreatedAt(): void;

    public function getVersionNumber(): ?int;

    public function setVersionNumber(?int $versionNumber): void;

    public function getData(): ?array;

    public function setData(?array $data): void;

    public function getMeta(): ?array;

    public function setMeta(?array $meta): void;

    public function setMetaField(string $field, mixed $value): void;

    public function getMetaField(string $field, mixed $default = null): mixed;

    public function getCompiledModules(): ?array;

    public function setCompiledModules(?array $compiledModules): void;

    public function getCompiled(): ?array;

    public function setCompiled(?array $compiled): void;

    public function isPublished(): bool;

    public function isLastVersion(): bool;

    public function deleteOnCleanup(): bool;

    public function isKeep(): bool;

    public function setKeep(bool $keep): void;

    public function addMedia(MediaInterface $media): void;

    public function removeMedia(MediaInterface $media): void;

    public function getMedias(): Collection;

    public function addRoute(RouteInterface $route): void;

    public function removeRoute(RouteInterface $route): void;

    public function getRoutes(): Collection;
}
