<?php

namespace Softspring\CmsBundle\Test\Unit\Config\EntityListener;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Entity\ContentVersion;
use Softspring\CmsBundle\EntityListener\ContentVersionListener;
use Softspring\CmsBundle\EntityTransformer\ContentVersionTransformer;

class ContentVersionListenerTest extends TestCase
{
    public function testPostLoad(): void
    {
        $version = new ContentVersion();
        $em = $this->createMock(EntityManager::class);
        $versionTransformer = $this->createMock(ContentVersionTransformer::class);
        $versionTransformer->expects($this->once())->method('untransform')->with($version, $em);

        $listener = new ContentVersionListener($versionTransformer);
        $listener->postLoad($version, new PostLoadEventArgs($version, $em));
    }

    public function testPreUpdate(): void
    {
        $version = new ContentVersion();
        $em = $this->createMock(EntityManager::class);
        $versionTransformer = $this->createMock(ContentVersionTransformer::class);
        $versionTransformer->expects($this->once())->method('transform')->with($version, $em);

        $listener = new ContentVersionListener($versionTransformer);
        $changeset = [];
        $listener->preUpdate($version, new PreUpdateEventArgs($version, $em, $changeset));
    }

    public function testPrePersist(): void
    {
        $version = new ContentVersion();
        $em = $this->createMock(EntityManager::class);
        $versionTransformer = $this->createMock(ContentVersionTransformer::class);
        $versionTransformer->expects($this->once())->method('transform')->with($version, $em);

        $listener = new ContentVersionListener($versionTransformer);
        $listener->prePersist($version, new PrePersistEventArgs($version, $em));
    }
}