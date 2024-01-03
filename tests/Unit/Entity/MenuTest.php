<?php

namespace Softspring\CmsBundle\Test\Unit\Config\Entity;

use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Entity\Menu;
use Softspring\CmsBundle\Entity\MenuItem;

class MenuTest extends TestCase
{
    public function testMenuIds(): void
    {
        $menu = new Menu();
        $this->assertNull($menu->getId());

        $reflection = new \ReflectionClass($menu);
        $property = $reflection->getProperty('id');
        $property->setValue($menu, 'test');
        $this->assertEquals('test', $menu->getId());
        $this->assertEquals('test', "$menu");
    }

    public function testMenuProperties(): void
    {
        $menu = new Menu();
        $this->assertNull($menu->getName());
        $this->assertNull($menu->getType());

        $menu->setName('test');
        $menu->setType('test');
        $this->assertEquals('test', $menu->getName());
        $this->assertEquals('test', $menu->getType());
    }

    public function testMenuItems(): void
    {
        $menu = new Menu();
        $this->assertCount(0, $menu->getItems());

        $item = new MenuItem();
        $menu->addItem($item);
        $this->assertCount(1, $menu->getItems());
        $this->assertEquals($item, $menu->getItems()->first());

        $menu->removeItem($item);
        $this->assertCount(0, $menu->getItems());
    }
}