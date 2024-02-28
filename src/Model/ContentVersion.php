<?php

namespace Softspring\CmsBundle\Model;

use Doctrine\Common\Collections\Collection;
use Softspring\MediaBundle\Model\MediaInterface;

abstract class ContentVersion implements ContentVersionInterface
{
    protected ?ContentInterface $content = null;

    protected ?int $origin = null;

    protected ?string $originDescription = null;

    protected ?string $note = null;

    protected ?string $layout = null;

    protected ?int $createdAt = null;

    protected ?int $versionNumber = null;

    protected ?array $seo = null;

    protected ?array $data = null;

    protected ?array $meta = null;

    protected ?array $compiledModules = null;

    protected ?array $compiled = null;

    protected bool $keep = false;

    protected ?Collection $medias = null;

    protected ?Collection $routes = null;

    public function getContent(): ?ContentInterface
    {
        return $this->content;
    }

    public function setContent(?ContentInterface $content): void
    {
        $this->content = $content;
    }

    public function getOrigin(): ?int
    {
        return $this->origin;
    }

    public function setOrigin(?int $origin): void
    {
        $this->origin = $origin;
    }

    public function getOriginDescription(): ?string
    {
        return $this->originDescription;
    }

    public function setOriginDescription(?string $originDescription): void
    {
        $this->originDescription = $originDescription;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): void
    {
        $this->note = $note;
    }

    public function getLayout(): ?string
    {
        return $this->layout;
    }

    public function setLayout(?string $layout): void
    {
        $this->layout = $layout;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt ? \DateTime::createFromFormat('U', "{$this->createdAt}") : null;
    }

    public function setCreatedAt(?\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt ? (int) $createdAt->format('U') : null;
    }

    public function autoSetCreatedAt(): void
    {
        if (!$this->createdAt) {
            $this->createdAt = time();
        }
    }

    public function getVersionNumber(): ?int
    {
        return $this->versionNumber;
    }

    public function setVersionNumber(?int $versionNumber): void
    {
        $this->versionNumber = $versionNumber;
    }

    public function getSeo(): ?array
    {
        return $this->seo;
    }

    public function setSeo(?array $seo): void
    {
        $this->seo = $seo;
    }

    public function getData(): ?array
    {
        if ($this->_getDataCallback) {
            $this->data = call_user_func($this->_getDataCallback, $this->data);
            $this->_getDataCallback = null;
        }

        return $this->data;
    }

    public function setData(?array $data): void
    {
        $this->data = $data;
    }

    public function getMeta(): ?array
    {
        return $this->meta;
    }

    public function setMeta(?array $meta): void
    {
        $this->meta = $meta;
    }

    public function setMetaField(string $field, mixed $value): void
    {
        $meta = $this->getMeta() ?? [];
        $meta[$field] = $value;
        $this->setMeta($meta);
    }

    //    /**
    //     * @throws \Exception
    //     */
    //    public function getPublishedAt(): ?\DateTime
    //    {
    //        if ($this->getContent()->getPublishedVersion() !== $this) {
    //            return null;
    //        }
    //
    //        $history = $this->getMetaField('history', []);
    //        $history = array_reverse($history);
    //
    //        foreach ($history as $historyItem) {
    //            if ($historyItem['action'] === 'publish') {
    //                return new \DateTime($historyItem['date']['date']);
    //            }
    //        }
    //
    //        return $this->getCreatedAt();
    //    }

    public function getMetaField(string $field, mixed $default = null): mixed
    {
        return $this->getMeta()[$field] ?? $default;
    }

    protected mixed $_getDataCallback = null;

    public function _setDataCallback(callable $getDataCallback): void
    {
        $this->_getDataCallback = $getDataCallback;
    }

    public function getCompiledModules(): ?array
    {
        return $this->compiledModules;
    }

    public function setCompiledModules(?array $compiledModules): void
    {
        $this->compiledModules = $compiledModules;
    }

    public function getCompiled(): ?array
    {
        return $this->compiled;
    }

    public function setCompiled(?array $compiled): void
    {
        $this->compiled = $compiled;
    }

    public function hasCompileErrors(): bool
    {
        foreach ($this->getCompiled() ?? [] as $siteCompiled) {
            if (is_array($siteCompiled)) {
                /* @deprecated: TODO to be removed in next versions */
                foreach ($siteCompiled as $localeCompiled) {
                    if (str_contains($localeCompiled, 'MODULE_RENDER_ERROR')) {
                        return true;
                    }
                }
            } elseif (is_string($siteCompiled)) {
                if (str_contains($siteCompiled, 'MODULE_RENDER_ERROR')) {
                    return true;
                }
            }
        }

        return false;
    }

    public function isPublished(): bool
    {
        return $this->getContent()?->getPublishedVersion() === $this;
    }

    public function isLastVersion(): bool
    {
        return $this->getContent()?->getVersions()->first() === $this;
    }

    public function deleteOnCleanup(): bool
    {
        return !$this->isKeep() && !$this->isPublished() && !$this->isLastVersion();
    }

    public function isKeep(): bool
    {
        return $this->keep;
    }

    public function setKeep(bool $keep): void
    {
        $this->keep = $keep;
    }

    public function addMedia(MediaInterface $media): void
    {
        if (!$this->medias->contains($media)) {
            $this->medias->add($media);
        }
    }

    public function removeMedia(MediaInterface $media): void
    {
        if ($this->medias->contains($media)) {
            $this->medias->removeElement($media);
        }
    }

    /**
     * @psalm-return ?Collection|MediaInterface[]
     */
    public function getMedias(): Collection
    {
        return $this->medias;
    }

    public function addRoute(RouteInterface $route): void
    {
        if (!$this->routes->contains($route)) {
            $this->routes->add($route);
        }
    }

    public function removeRoute(RouteInterface $route): void
    {
        if ($this->routes->contains($route)) {
            $this->routes->removeElement($route);
        }
    }

    /**
     * @psalm-return ?Collection|RouteInterface[]
     */
    public function getRoutes(): Collection
    {
        return $this->routes;
    }
}
