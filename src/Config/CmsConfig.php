<?php

namespace Softspring\CmsBundle\Config;

use Softspring\CmsBundle\Config\Exception\InvalidBlockException;
use Softspring\CmsBundle\Config\Exception\InvalidContentException;
use Softspring\CmsBundle\Config\Exception\InvalidLayoutException;
use Softspring\CmsBundle\Config\Exception\InvalidMenuException;
use Softspring\CmsBundle\Config\Exception\InvalidModuleException;

class CmsConfig
{
    protected array $layouts;
    protected array $modules;
    protected array $contents;
    protected array $menus;
    protected array $blocks;
    protected array $sites;

    public function __construct(array $layouts, array $modules, array $contents, array $menus, array $blocks)
    {
        $this->layouts = $layouts;
        $this->modules = $modules;
        $this->contents = $contents;
        $this->menus = $menus;
        $this->blocks = $blocks;
    }

    public function getLayouts(): array
    {
        return $this->layouts;
    }

    /**
     * @throws InvalidLayoutException
     */
    public function getLayout(string $id, bool $required = true): ?array
    {
        if ($required && !isset($this->layouts[$id])) {
            throw new InvalidLayoutException($id, $this->layouts);
        }

        return $this->layouts[$id] ?? null;
    }

    public function getModules(): array
    {
        return $this->modules;
    }

    /**
     * @throws InvalidModuleException
     */
    public function getModule(string $id, bool $required = true): ?array
    {
        if ($required && !isset($this->modules[$id])) {
            throw new InvalidModuleException($id, $this->modules);
        }

        return $this->modules[$id] ?? null;
    }

    public function getContents(): array
    {
        return $this->contents;
    }

    /**
     * @throws InvalidContentException
     */
    public function getContent(string $id, bool $required = true): ?array
    {
        if ($required && !isset($this->contents[$id])) {
            throw new InvalidContentException($id, $this->contents);
        }

        return $this->contents[$id] ?? null;
    }

    public function getContentMappings(): array
    {
        $mappings = [];

        foreach ($this->getContents() as $contentName => $content) {
            $mappings[$contentName] = $content['entity_class'];
        }

        return $mappings;
    }

    public function getMenus(): array
    {
        return $this->menus;
    }

    /**
     * @throws InvalidMenuException
     */
    public function getMenu(string $id, bool $required = true): ?array
    {
        if ($required && !isset($this->menus[$id])) {
            throw new InvalidMenuException($id, $this->menus);
        }

        return $this->menus[$id] ?? null;
    }

    public function getBlocks(): array
    {
        return $this->blocks;
    }

    /**
     * @throws InvalidBlockException
     */
    public function getBlock(string $id, bool $required = true): ?array
    {
        if ($required && !isset($this->blocks[$id])) {
            throw new InvalidBlockException($id, $this->blocks);
        }

        return $this->blocks[$id] ?? null;
    }

    public function getSites(): array
    {
        return $this->sites;
    }
}
