<?php

namespace Softspring\CmsBundle\Model;

use Doctrine\Common\Collections\Collection;
use Softspring\CmsBundle\Entity\AbstractModule;
use Softspring\CmsBundle\Entity\Embeddable\Seo;

interface PageInterface
{
    public function getId(): ?string;

    public function getName(): ?string;

    public function setName(?string $name): void;

    /**
     * @return AbstractModule[]|Collection
     */
    public function getModules(): Collection;

    public function addModule(AbstractModule $module): void;

    public function removeModule(AbstractModule $module): void;

    public function getLayout(): ?LayoutInterface;

    public function setLayout(?LayoutInterface $layout): void;

    public function getSeo(): Seo;

    public function setSeo(Seo $seo): void;

    /**
     * @return RouteInterface[]|Collection
     */
    public function getRoutes(): Collection;
}