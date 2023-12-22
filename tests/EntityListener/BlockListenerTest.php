<?php

namespace Softspring\CmsBundle\Test\Config\EntityListener;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Entity\Block;
use Softspring\CmsBundle\EntityListener\BlockListener;
use Softspring\CmsBundle\EntityTransformer\BlockTransformer;

class BlockListenerTest extends TestCase
{
    public function testPostLoad(): void
    {
        $block = new Block();
        $em = $this->createMock(EntityManager::class);
        $blockTransformer = $this->createMock(BlockTransformer::class);
        $blockTransformer->expects($this->once())->method('untransform')->with($block, $em);

        $listener = new BlockListener($blockTransformer);
        $listener->postLoad($block, new PostLoadEventArgs($block, $em));
    }

    public function testPreUpdate(): void
    {
        $block = new Block();
        $em = $this->createMock(EntityManager::class);
        $blockTransformer = $this->createMock(BlockTransformer::class);
        $blockTransformer->expects($this->once())->method('transform')->with($block, $em);

        $listener = new BlockListener($blockTransformer);
        $changeset = [];
        $listener->preUpdate($block, new PreUpdateEventArgs($block, $em, $changeset));
    }

    public function testPrePersist(): void
    {
        $block = new Block();
        $em = $this->createMock(EntityManager::class);
        $blockTransformer = $this->createMock(BlockTransformer::class);
        $blockTransformer->expects($this->once())->method('transform')->with($block, $em);

        $listener = new BlockListener($blockTransformer);
        $listener->prePersist($block, new PrePersistEventArgs($block, $em));
    }
}