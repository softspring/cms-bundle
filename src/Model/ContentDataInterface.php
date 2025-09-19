<?php

namespace Softspring\CmsBundle\Model;

use Doctrine\Common\Collections\Collection;
use Softspring\CmsSectionsPlugin\Model\SectionInterface;
use Softspring\MediaBundle\Model\MediaInterface;

interface ContentDataInterface
{
    public function _setDataCallback(callable $getDataCallback): void;

    public function getData(): ?array;

    public function getRawData(): ?array;

    public function setData(?array $data): void;

    public function addMedia(MediaInterface $media): void;

    public function removeMedia(MediaInterface $media): void;

    public function getMedias(): Collection;

    public function addRoute(RouteInterface $route): void;

    public function removeRoute(RouteInterface $route): void;

    public function getRoutes(): Collection;

    public function addSection(SectionInterface $section): void;

    public function removeSection(SectionInterface $section): void;

    public function getSections(): ?Collection;
}
