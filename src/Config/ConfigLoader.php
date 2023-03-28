<?php

namespace Softspring\CmsBundle\Config;

use Softspring\CmsBundle\Config\Exception\MissingLayoutsException;
use Softspring\CmsBundle\Config\Model\Block;
use Softspring\CmsBundle\Config\Model\Content;
use Softspring\CmsBundle\Config\Model\Layout;
use Softspring\CmsBundle\Config\Model\Menu;
use Softspring\CmsBundle\Config\Model\Module;
use Softspring\CmsBundle\Config\Model\Site;
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
    protected array $collectionPaths;

    public function __construct(ContainerInterface $container, array $collectionPaths = [])
    {
        $this->container = $container;
        $this->collectionPaths = $collectionPaths;
        $this->initDirectories();
    }

    protected function initDirectories(): void
    {
        $fs = new Filesystem();

        $cmsPath = $this->container->getParameter('kernel.project_dir').'/cms';
        if (!$fs->exists($cmsPath)) {
            $fs->mkdir($cmsPath);
        }

        $blocksPath = $this->container->getParameter('kernel.project_dir').'/cms/blocks';
        if (!$fs->exists($blocksPath)) {
            $fs->mkdir($blocksPath);
        }

        $contentsPath = $this->container->getParameter('kernel.project_dir').'/cms/contents';
        if (!$fs->exists($contentsPath)) {
            $fs->mkdir($contentsPath);
        }

        $layoutsPath = $this->container->getParameter('kernel.project_dir').'/cms/layouts';
        if (!$fs->exists($layoutsPath)) {
            $fs->mkdir($layoutsPath);
        }

        $menusPath = $this->container->getParameter('kernel.project_dir').'/cms/menus';
        if (!$fs->exists($menusPath)) {
            $fs->mkdir($menusPath);
        }

        $modulesPath = $this->container->getParameter('kernel.project_dir').'/cms/modules';
        if (!$fs->exists($modulesPath)) {
            $fs->mkdir($modulesPath);
        }

        $sitesPath = $this->container->getParameter('kernel.project_dir').'/cms/sites';
        if (!$fs->exists($sitesPath)) {
            $fs->mkdir($sitesPath);
        }
    }

    public function getModules(ContainerBuilder $containerBuilder): array
    {
        $processor = new Processor();
        $modules = [];

        $configurations = $this->readConfigurations($containerBuilder, 'modules', 'module');

        foreach ($configurations as $moduleName => $moduleConfigs) {
            $modules[$moduleName] = $processor->processConfiguration(new Module($moduleName), $moduleConfigs);
            $modules[$moduleName]['_id'] = $moduleName;
            $modules[$moduleName]['revision_migration_scripts'] = [];

            foreach ($this->collectionPaths as $collectionPath) {
                $elementsPath = $this->container->getParameter('kernel.project_dir').'/'.trim($collectionPath, '/').'/modules/'.$moduleName;
                if (is_file("$elementsPath/migrate.php")) {
                    $modules[$moduleName]['revision_migration_scripts'][] = "$elementsPath/migrate.php";
                }
            }
        }

        return $modules;
    }

    /**
     * @throws MissingLayoutsException
     */
    public function getLayouts(ContainerBuilder $containerBuilder): array
    {
        $processor = new Processor();
        $layouts = [];

        $configurations = $this->readConfigurations($containerBuilder, 'layouts', 'layout');

        foreach ($configurations as $layoutName => $layoutConfigs) {
            $layouts[$layoutName] = $processor->processConfiguration(new Layout($layoutName), $layoutConfigs);
            $layouts[$layoutName]['_id'] = $layoutName;
        }

        if (empty($layouts)) {
            throw new MissingLayoutsException('No CMS layouts are configured. At least one layout is required');
        }

        return $layouts;
    }

    public function getContents(ContainerBuilder $containerBuilder): array
    {
        $processor = new Processor();
        $contents = [];

        $configurations = $this->readConfigurations($containerBuilder, 'contents', 'content');

        foreach ($configurations as $contentName => $contentConfigs) {
            $contents[$contentName] = $processor->processConfiguration(new Content($contentName), $contentConfigs);
            $contents[$contentName]['_id'] = $contentName;
        }

        return $contents;
    }

    public function getMenus(ContainerBuilder $containerBuilder): array
    {
        $processor = new Processor();
        $menus = [];

        $configurations = $this->readConfigurations($containerBuilder, 'menus', 'menu');

        foreach ($configurations as $menuName => $menuConfigs) {
            $menus[$menuName] = $processor->processConfiguration(new Menu($menuName), $menuConfigs);
            $menus[$menuName]['_id'] = $menuName;
        }

        return $menus;
    }

    public function getBlocks(ContainerBuilder $containerBuilder): array
    {
        $processor = new Processor();
        $blocks = [];

        $configurations = $this->readConfigurations($containerBuilder, 'blocks', 'block');

        foreach ($configurations as $blockName => $blockConfigs) {
            $blocks[$blockName] = $processor->processConfiguration(new Block($blockName), $blockConfigs);
            $blocks[$blockName]['_id'] = $blockName;
        }

        return $blocks;
    }

    public function getSites(ContainerBuilder $containerBuilder): array
    {
        $processor = new Processor();
        $sites = [];

        $configurations = $this->readConfigurations($containerBuilder, 'sites', 'site');

        foreach ($configurations as $siteName => $siteConfigs) {
            $sites[$siteName] = $processor->processConfiguration(new Site($siteName), $siteConfigs);
            $sites[$siteName]['_id'] = $siteName;
        }

        return $sites;
    }

    protected function readConfigurations(ContainerBuilder $containerBuilder, string $elementPath, string $elementType): array
    {
        $configurations = [];

        foreach ($this->collectionPaths as $collectionPath) {
            $elementsPath = $this->container->getParameter('kernel.project_dir').'/'.trim($collectionPath, '/').'/'.$elementPath;
            if (is_dir($elementsPath)) {
                foreach ((new Finder())->in($elementsPath)->directories()->depth(0) as $elementFilePath) {
                    $elementName = $elementFilePath->getFilename();
                    $configurations[$elementName][] = Yaml::parseFile("$elementFilePath/config.yaml")[$elementType];
                    // force reload cache if some change has been done in cms folder
                    $containerBuilder->addResource(new FileResource("$elementFilePath/config.yaml"));
                }
            }
        }

        return $configurations;
    }
}
