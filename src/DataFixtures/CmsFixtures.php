<?php

namespace Softspring\CmsBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Softspring\CmsBundle\Manager\ContentManagerInterface;
use Softspring\CmsBundle\Manager\MenuItemManagerInterface;
use Softspring\CmsBundle\Manager\MenuManagerInterface;
use Softspring\CmsBundle\Manager\RouteManagerInterface;
use Softspring\CmsBundle\Manager\RoutePathManagerInterface;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\Model\MenuInterface;
use Softspring\CmsBundle\Model\MenuItemInterface;
use Softspring\CmsBundle\Model\RouteInterface;
use Softspring\CmsBundle\Model\RoutePathInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Yaml\Yaml;

class CmsFixtures extends Fixture implements FixtureGroupInterface
{
    protected ContainerInterface $container;
    protected ContentManagerInterface $contentManager;
    protected RouteManagerInterface $routeManager;
    protected RoutePathManagerInterface $routePathManager;
    protected MenuManagerInterface $menuManager;
    protected MenuItemManagerInterface $menuItemManager;
    protected string $fixturesPath;

    public function __construct(ContainerInterface $container, ContentManagerInterface $contentManager, RouteManagerInterface $routeManager, RoutePathManagerInterface $routePathManager, MenuManagerInterface $menuManager, MenuItemManagerInterface $menuItemManager)
    {
        $this->container = $container;
        $this->contentManager = $contentManager;
        $this->routeManager = $routeManager;
        $this->routePathManager = $routePathManager;
        $this->menuManager = $menuManager;
        $this->menuItemManager = $menuItemManager;
        $this->fixturesPath = $this->container->getParameter('kernel.project_dir').'/cms/fixtures';
    }

    public function load(ObjectManager $manager)
    {
        $this->preloadRoutes($manager);
        $this->loadContents($manager);
        $this->loadMenus($manager);
        $this->loadRoutes($manager);
        $manager->flush();
    }

    public function loadContents(ObjectManager $manager)
    {
        foreach ((new Finder())->in("$this->fixturesPath/contents")->files() as $contentFile) {
            $data = Yaml::parseFile($contentFile->getRealPath());

            $id = $contentFile->getFilenameWithoutExtension();
            $key = key($data);
            $contentConfig = current($data);

            $content = $this->createContent($key, $contentConfig['name'], null, $contentConfig['extra']);

            $propertyAccessor = PropertyAccess::createPropertyAccessor();

            foreach ($contentConfig['fields']??[] as $field => $value) {
                if (isset($value['_reference'])) {
                    $propertyAccessor->setValue($content, $field, $this->getReference($value['_reference']));
                } else {
                    $propertyAccessor->setValue($content, $field, $value);
                }
            }

            foreach ($contentConfig['versions'] as $version) {
                $data = $version['data'];
                $this->replaceModuleFixtureReferences($data);
                $this->createVersion($content, $version['layout'], $data);
            }

            $this->addReference("content___$id", $content);

            $manager->persist($content);
        }
    }

    protected function replaceModuleFixtureReferences(&$data)
    {
        foreach ($data as $key => &$value) {
            if (is_array($value)) {
                if (isset($value['_reference'])) {
                    $value = $this->getReference($value['_reference']);
                } else {
                    $this->replaceModuleFixtureReferences($value);
                }
            }
        }
    }

    public function loadMenus(ObjectManager $manager)
    {
        foreach ((new Finder())->in("$this->fixturesPath/menus")->files() as $menuFile) {
            $data = Yaml::parseFile($menuFile->getRealPath());

            $id = $menuFile->getFilenameWithoutExtension();
            $key = key($data);
            $menuConfig = current($data);

            $menu = $this->createMenu($menuConfig['type'], $menuConfig['name']);

            foreach ($menuConfig['items'] as $item) {
                $route = null;

                if (!empty($item['route']['_reference'])) {
                    /** @var RouteInterface $route */
                    $route = $this->getReference($item['route']['_reference']);
                }

                $this->createMenuItem($menu, $item['text'], $route);
            }

            $this->addReference("menu___$id", $menu);

            $manager->persist($menu);
        }
    }

    public function preloadRoutes(ObjectManager $manager)
    {
        foreach ((new Finder())->in("$this->fixturesPath/routes")->files() as $routeFile) {
            $data = Yaml::parseFile($routeFile->getRealPath());
            $routeConfig = current($data);
            $route = $this->createRoute($routeConfig['id']);

            foreach ($routeConfig['paths'] as $paths) {
                $this->createRoutePath($route, $paths['path'], $paths['cache_ttl'] ?? null);
            }

            $this->addReference("route___{$routeConfig['id']}", $route);
        }
    }

    public function loadRoutes(ObjectManager $manager)
    {
        foreach ((new Finder())->in("$this->fixturesPath/routes")->files() as $routeFile) {
            $data = Yaml::parseFile($routeFile->getRealPath());
            $routeConfig = current($data);

            /** @var RouteInterface $route */
            $route = $this->getReference("route___{$routeConfig['id']}");

            if (!empty($routeConfig['content'])) {
                $route->setType(RouteInterface::TYPE_CONTENT);
                $route->setContent($this->getReference("content___{$routeConfig['content']}"));
            }

            $manager->persist($route);
        }
    }

    protected function createPage(string $name, string $layout = null, array $data = null, array $extraData = []): ContentInterface
    {
        return $this->createContent('page', $name, $layout, $data, $extraData);
    }

    protected function createContent(string $contentType, string $name, string $layout = null, array $data = null, array $extraData = []): ContentInterface
    {
        $content = $this->contentManager->createEntity($contentType);
        $content->setName($name);
        $content->setExtraData($extraData);

        if ($layout && $data) {
            $this->createVersion($content, $layout, $data);
        }

        return $content;
    }

    protected function createVersion(ContentInterface $content, string $layout, array $data): ContentVersionInterface
    {
        $version = $this->contentManager->createVersion($content);
        $version->setLayout($layout);
        $version->setData($data);

        return $version;
    }

    protected function createRoute(string $routeName, string $path = null, ContentInterface $content = null): RouteInterface
    {
        $route = $this->routeManager->createEntity();
        $route->setId($routeName);
        $route->setType(RouteInterface::TYPE_UNKNOWN);

        if ($content) {
            $route->setContent($content);
            $route->setType(RouteInterface::TYPE_CONTENT);
        }

        foreach ($route->getPaths() as $existingPath) {
            $route->removePath($existingPath);
        }

        if ($path) {
            $routePath = $this->routePathManager->createEntity();
            $routePath->setPath($path);
        }

        return $route;
    }

    protected function createRoutePath(RouteInterface $route, string $path, ?int $cacheTtl = null): RoutePathInterface
    {
        $route->addPath($routePath = $this->routePathManager->createEntity());

        $routePath->setPath($path);
        $routePath->setCacheTtl($cacheTtl);

        return $routePath;
    }

    protected function createMenu(string $menuType, string $name): MenuInterface
    {
        $menu = $this->menuManager->createEntity($menuType);
        $menu->setType($menuType);
        $menu->setName($name);

        return $menu;
    }

    protected function createMenuItem(MenuInterface $menu, string $text, ?RouteInterface $route = null): MenuItemInterface
    {
        $item = $this->menuItemManager->createEntity();
        $menu->addItem($item);
        $item->setText($text);
        $item->setRoute($route);

        return $item;
    }

    public static function getGroups(): array
    {
        return ['sfs_cms'];
    }
}