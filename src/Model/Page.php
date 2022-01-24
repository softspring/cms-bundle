<?php

namespace Softspring\CmsBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Softspring\CmsBundle\Entity\AbstractModule;
use Softspring\CmsBundle\Entity\Embeddable\Seo;
use Softspring\TranslationBundle\Configuration\TranslationsEmbed;

abstract class Page implements PageInterface
{
    protected ?string $name;

    protected ?LayoutInterface $layout;

    /**
     * @var AbstractModule[]|Collection
     */
    protected Collection $modules;

    /**
     * @TranslationsEmbed(prefix="seo")
     */
    protected Seo $seo;

    /**
     * @var RouteInterface[]|Collection
     */
    protected Collection $routes;

    public function __construct()
    {
        $this->modules = new ArrayCollection();
        $this->routes = new ArrayCollection();
        $this->seo = new Seo();
    }

    public function __toString(): string
    {
        return "{$this->getId()}";
    }

    abstract public function getId(): ?string;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return AbstractModule[]|Collection
     */
    public function getModules(): Collection
    {
        return $this->modules;
    }

    /**
     * @param AbstractModule $module
     */
    public function addModule(AbstractModule $module): void
    {
        if (!$this->getModules()->contains($module)) {
            $this->getModules()->add($module);
            $module->setPage($this);
        }
    }

    /**
     * @param AbstractModule $module
     */
    public function removeModule(AbstractModule $module): void
    {
        if ($this->getModules()->contains($module)) {
            $this->getModules()->removeElement($module);
            $module->setPage(null);
        }
    }

    public function getLayout(): ?LayoutInterface
    {
        return $this->layout;
    }

    public function setLayout(?LayoutInterface $layout): void
    {
        $this->layout = $layout;
    }

    public function getSeo(): Seo
    {
        return $this->seo;
    }

    public function setSeo(Seo $seo): void
    {
        $this->seo = $seo;
    }

    /**
     * @return RouteInterface[]|Collection
     */
    public function getRoutes(): Collection
    {
        return $this->routes;
    }
}