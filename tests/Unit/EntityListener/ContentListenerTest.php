<?php

namespace Softspring\CmsBundle\Test\Unit\Config\EntityListener;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Entity\Page;
use Softspring\CmsBundle\EntityListener\ContentListener;
use Softspring\CmsBundle\EntityTransformer\ContentTransformer;

class ContentListenerTest extends TestCase
{
    public function testPostLoad(): void
    {
        $content = new Page();
        $em = $this->createMock(EntityManager::class);
        $contentTransformer = $this->createMock(ContentTransformer::class);
        $contentTransformer->expects($this->once())->method('untransform')->with($content, $em);

        $listener = new ContentListener($contentTransformer);
        $listener->postLoad($content, new PostLoadEventArgs($content, $em));
    }

    public function testPreUpdate(): void
    {
        $content = new Page();
        $em = $this->createMock(EntityManager::class);
        $contentTransformer = $this->createMock(ContentTransformer::class);
        $contentTransformer->expects($this->once())->method('transform')->with($content, $em);

        $listener = new ContentListener($contentTransformer);
        $changeset = [];
        $listener->preUpdate($content, new PreUpdateEventArgs($content, $em, $changeset));
    }

    public function testPrePersist(): void
    {
        $content = new Page();
        $em = $this->createMock(EntityManager::class);
        $contentTransformer = $this->createMock(ContentTransformer::class);
        $contentTransformer->expects($this->once())->method('transform')->with($content, $em);

        $listener = new ContentListener($contentTransformer);
        $listener->prePersist($content, new PrePersistEventArgs($content, $em));
    }
}