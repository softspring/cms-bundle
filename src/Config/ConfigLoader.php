<?php

namespace Softspring\CmsBundle\Config;

use Softspring\CmsBundle\Config\Model\Content;
use Softspring\CmsBundle\Config\Model\Layout;
use Softspring\CmsBundle\Config\Model\Module;
use Softspring\CmsBundle\Entity\Page;
use Softspring\CmsBundle\Form\Admin\Content\ContentCreateForm;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

class ConfigLoader
{
    protected ContainerInterface $container;
    protected string $cmsPath;
    protected string $blocksPath;
    protected string $contentsPath;
    protected string $layoutsPath;
    protected string $modulesPath;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->cmsPath = $this->container->getParameter('kernel.project_dir').'/cms';
        $this->blocksPath = "{$this->cmsPath}/blocks";
        $this->contentsPath = "{$this->cmsPath}/contents";
        $this->layoutsPath = "{$this->cmsPath}/layouts";
        $this->modulesPath = "{$this->cmsPath}/modules";
        $this->initDirectories();
    }

    protected function initDirectories(): void
    {
        $fs = new Filesystem();

        if (!$fs->exists($this->cmsPath)) {
            $fs->mkdir($this->cmsPath);
        }

        if (!$fs->exists($this->blocksPath)) {
            $fs->mkdir($this->blocksPath);
        }

        if (!$fs->exists($this->contentsPath)) {
            $fs->mkdir($this->contentsPath);
        }

        if (!$fs->exists($this->layoutsPath)) {
            $fs->mkdir($this->layoutsPath);
        }

        if (!$fs->exists($this->modulesPath)) {
            $fs->mkdir($this->modulesPath);
        }
    }

    public function getModules(ContainerBuilder $containerBuilder): array
    {
        $processor = new Processor();
        $modules = [];

        foreach ((new Finder())->in($this->modulesPath)->directories()->depth(0) as $modulePath) {
            $moduleName = $modulePath->getFilename();
            $moduleConfiguration = new Module($moduleName);
            $modules[$moduleName] = $processor->processConfiguration($moduleConfiguration, Yaml::parseFile("$modulePath/config.yaml"));
            // force reload cache if some change has been done in cms folder
            $containerBuilder->addResource(new FileResource("$modulePath/config.yaml"));
        }

        return $modules;
    }

    public function getLayouts(ContainerBuilder $containerBuilder): array
    {
        $processor = new Processor();
        $layouts = [];

        foreach ((new Finder())->in($this->layoutsPath)->directories()->depth(0) as $layoutPath) {
            $layoutName = $layoutPath->getFilename();
            $layoutConfiguration = new Layout($layoutName);
            $layouts[$layoutName] = $processor->processConfiguration($layoutConfiguration, Yaml::parseFile("$layoutPath/config.yaml"));
            // force reload cache if some change has been done in cms folder
            $containerBuilder->addResource(new FileResource("$layoutPath/config.yaml"));
        }

        return $layouts;
    }

    public function getContents(ContainerBuilder $containerBuilder): array
    {
        $processor = new Processor();
        $contents = [];

        $contentConfiguration = new Content('page');
        $contents['page'] = $processor->processConfiguration($contentConfiguration, [
            'content' => [
                'revision' => 1,
                'entity_class' => Page::class,
            ]
        ]);
        $contents['page']['_id'] = 'page';

        foreach ((new Finder())->in($this->contentsPath)->directories()->depth(0) as $contentPath) {
            $contentName = $contentPath->getFilename();
            $contentConfiguration = new Content($contentName);
            $contents[$contentName] = $processor->processConfiguration($contentConfiguration, Yaml::parseFile("$contentPath/config.yaml"));
            $contents[$contentName]['_id'] = $contentName;
            // force reload cache if some change has been done in cms folder
            $containerBuilder->addResource(new FileResource("$contentPath/config.yaml"));
        }

        return $contents;
    }

    public function getBlocks(): array
    {
        return [];
    }
}