<?php

namespace Softspring\CmsBundle\Test\Config\Entity;

use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Entity\Block;

class BlockTest extends TestCase
{
    public function testIds(): void
    {
        $block = new Block();
        $this->assertNull($block->getId());

        $reflection = new \ReflectionClass($block);
        $property = $reflection->getProperty('id');
        $property->setValue($block, 'test');
        $this->assertEquals('test', $block->getId());
        $this->assertEquals('test', "$block");
    }

    public function testProperties(): void
    {
        $block = new Block();
        $this->assertNull($block->getName());
        $this->assertNull($block->getType());
        $this->assertNull($block->getData());

        $block->setName('test');
        $block->setType('test');
        $block->setData(['test']);
        $this->assertEquals('test', $block->getName());
        $this->assertEquals('test', $block->getType());
        $this->assertEquals(['test'], $block->getData());
    }
}