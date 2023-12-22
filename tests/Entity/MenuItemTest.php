<?php

namespace Softspring\CmsBundle\Test\Config\Entity;

use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Entity\Menu;
use Softspring\CmsBundle\Entity\MenuItem;
use Softspring\CmsBundle\Model\MenuItemInterface;

class MenuItemTest extends TestCase
{
    public function testMenuIds(): void
    {
        $item = new MenuItem();
        $this->assertNull($item->getId());

        $reflection = new \ReflectionClass($item);
        $property = $reflection->getProperty('id');
        $property->setValue($item, 'test');
        $this->assertEquals('test', $item->getId());
    }

    public function testMenuProperties(): void
    {
        $item = new MenuItem();
        $this->assertNull($item->getMenu());
        $this->assertEquals(MenuItemInterface::TYPE_ROUTE, $item->getType());
        $this->assertNull($item->getText());
        $this->assertNull($item->getOptions());
        $this->assertNull($item->getSymfonyRoute());
        $this->assertNull($item->getParent());
        $this->assertCount(0, $item->getItems());

        $item->setMenu($menu = new Menu());
        $item->setType(MenuItemInterface::TYPE_UNKNOWN);
        $item->setText(['test']);
        $item->setOptions(['test']);
        $item->setSymfonyRoute(['test']);
        $this->assertEquals($menu, $item->getMenu());
        $this->assertEquals(MenuItemInterface::TYPE_UNKNOWN, $item->getType());
        $this->assertEquals(['test'], $item->getText());
        $this->assertEquals(['test'], $item->getOptions());
        $this->assertEquals(['test'], $item->getSymfonyRoute());
    }

    public function testMenuItems(): void
    {
        $item = new MenuItem();
        $this->assertCount(0, $item->getItems());

        $child = new MenuItem();
        $item->addItem($child);
        $this->assertCount(1, $item->getItems());
        $this->assertEquals($child, $item->getItems()->first());

        $item->removeItem($child);
        $this->assertCount(0, $item->getItems());
    }
}
