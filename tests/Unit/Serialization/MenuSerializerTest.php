<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Test\Unit\Serialization;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Model\MenuInterface;
use Softspring\CmsBundle\Model\MenuItemInterface;
use Softspring\CmsBundle\Serialization\MenuSerializer;

class MenuSerializerTest extends TestCase
{
    public function testItSerializesMenuSummaryAndNestedItems(): void
    {
        $serializer = new MenuSerializer();
        $child = $this->item('child', ['en' => 'Child'], []);
        $root = $this->item('root', ['en' => 'Root'], [$child]);
        $orphanChild = $this->item('orphan-child', ['en' => 'Hidden child'], [], $root);

        $menu = $this->createMock(MenuInterface::class);
        $menu->method('getId')->willReturn('main-menu');
        $menu->method('getType')->willReturn('header');
        $menu->method('getName')->willReturn('Main menu');
        $menu->method('getData')->willReturn(['css_class' => 'navbar']);
        $menu->method('getItems')->willReturn(new ArrayCollection([$root, $orphanChild]));

        self::assertSame([
            'id' => 'main-menu',
            'type' => 'header',
            'name' => 'Main menu',
            'data' => ['css_class' => 'navbar'],
            'items' => [[
                'id' => 'root',
                'type' => MenuItemInterface::TYPE_ROUTE,
                'text' => ['en' => 'Root'],
                'symfonyRoute' => ['route' => 'homepage'],
                'options' => ['target' => '_self'],
                'items' => [[
                    'id' => 'child',
                    'type' => MenuItemInterface::TYPE_ROUTE,
                    'text' => ['en' => 'Child'],
                    'symfonyRoute' => ['route' => 'homepage'],
                    'options' => ['target' => '_self'],
                    'items' => [],
                ]],
            ]],
        ], $serializer->detail($menu));
    }

    /**
     * @param MenuItemInterface[] $children
     */
    private function item(string $id, array $text, array $children, ?MenuItemInterface $parent = null): MenuItemInterface
    {
        $item = $this->createMock(MenuItemInterface::class);
        $item->method('getId')->willReturn($id);
        $item->method('getType')->willReturn(MenuItemInterface::TYPE_ROUTE);
        $item->method('getText')->willReturn($text);
        $item->method('getSymfonyRoute')->willReturn(['route' => 'homepage']);
        $item->method('getOptions')->willReturn(['target' => '_self']);
        $item->method('getParent')->willReturn($parent);
        $item->method('getItems')->willReturn(new ArrayCollection($children));

        return $item;
    }
}
