<?php

namespace Softspring\CmsBundle\Model\Traits;

use Doctrine\Common\Collections\Collection;
use Softspring\CmsBundle\Model\RouteInterface;
use Softspring\MediaBundle\Model\MediaInterface;

trait ContentDataTrait
{
    protected ?array $data = null;

    protected ?Collection $medias = null;

    protected ?Collection $routes = null;

    protected mixed $_getDataCallback = null;

    public function _setDataCallback(callable $getDataCallback): void
    {
        $this->_getDataCallback = $getDataCallback;
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
