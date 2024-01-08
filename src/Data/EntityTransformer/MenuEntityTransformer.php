<?php

namespace Softspring\CmsBundle\Data\EntityTransformer;

use Softspring\CmsBundle\Data\Exception\InvalidElementException;
use Softspring\CmsBundle\Data\Exception\ReferenceNotFoundException;
use Softspring\CmsBundle\Data\Exception\RunPreloadBeforeImportException;
use Softspring\CmsBundle\Data\ReferencesRepository;
use Softspring\CmsBundle\Manager\MenuItemManagerInterface;
use Softspring\CmsBundle\Manager\MenuManagerInterface;
use Softspring\CmsBundle\Model\MenuInterface;
use Softspring\CmsBundle\Model\MenuItemInterface;
use Softspring\CmsBundle\Utils\Slugger;

/**
 * @deprecated this class is deprecated, and will be removed on 6.0 version, when fixtures will be refactored to use serializer
 */
class MenuEntityTransformer implements EntityTransformerInterface
{
    protected MenuManagerInterface $menuManager;
    protected MenuItemManagerInterface $menuItemManager;

    public function __construct(MenuManagerInterface $menuManager, MenuItemManagerInterface $menuItemManager)
    {
        $this->menuManager = $menuManager;
        $this->menuItemManager = $menuItemManager;
    }

    public static function getPriority(): int
    {
        return 0;
    }

    public function supports(string $type, $data = null): bool
    {
        if ('menus' === $type) {
            return true;
        }

        return false;
    }

    public function export(object $element, &$files = []): array
    {
        if (!$element instanceof MenuInterface) {
            throw new InvalidElementException(sprintf('%s dumper requires that $element to be an instance of %s, %s given.', get_called_class(), MenuInterface::class, get_class($element)));
        }
        $menu = $element;

        $dump = [
            'menu' => [
                'type' => $menu->getType(),
                'name' => $menu->getName(),
                'items' => [],
            ],
        ];

        foreach ($menu->getItems() as $item) {
            $dump['menu']['items'][] = [
                'text' => $item->getText(),
                'symfony_route' => $item->getSymfonyRoute(),
            ];
        }

        return $dump;
    }

    public function preload(array $data, ReferencesRepository $referencesRepository): void
    {
        $menuId = Slugger::lowerSlug($data['menu']['name']);
        $referencesRepository->addReference("menu___{$menuId}", $this->menuManager->createEntity($data['menu']['type']));
    }

    public function import(array $data, ReferencesRepository $referencesRepository, array $options = []): MenuInterface
    {
        $menuId = Slugger::lowerSlug($data['menu']['name']);

        try {
            /** @var MenuInterface $menu */
            $menu = $referencesRepository->getReference("menu___{$menuId}", true);
        } catch (ReferenceNotFoundException $e) {
            throw new RunPreloadBeforeImportException('You must call preload() method before importing', 0, $e);
        }

        $menu->setName($data['menu']['name']);

        foreach ($data['menu']['items'] as $item) {
            $this->createMenuItem($menu, $item['text'], $item['symfony_route']);
        }

        return $menu;
    }

    public function createMenuItem(MenuInterface $menu, array $text, array $route = null): MenuItemInterface
    {
        $item = $this->menuItemManager->createEntity();
        $menu->addItem($item);
        $item->setText($text);
        $item->setSymfonyRoute($route);

        return $item;
    }
}
