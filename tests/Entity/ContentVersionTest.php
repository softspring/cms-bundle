<?php

namespace Softspring\CmsBundle\Test\Config\Entity;

use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Entity\ContentVersion;
use Softspring\CmsBundle\Entity\Page;
use Softspring\CmsBundle\Entity\Route;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\MediaBundle\Entity\Media;

class ContentVersionTest extends TestCase
{
    public function testIds(): void
    {
        $version = new ContentVersion();
        $this->assertNull($version->getId());

        $reflection = new \ReflectionClass($version);
        $property = $reflection->getProperty('id');
        $property->setValue($version, 'test');
        $this->assertEquals('test', $version->getId());
        $this->assertEquals('test', "$version");
    }

    public function testContent(): void
    {
        $version = new ContentVersion();
        $this->assertNull($version->getContent());

        $version->setContent($page = new Page());
        $this->assertEquals($page, $version->getContent());
    }

    public function testOrigin(): void
    {
        $version = new ContentVersion();
        $this->assertNull($version->getOrigin());

        $version->setOrigin(ContentVersionInterface::ORIGIN_EDIT);
        $this->assertEquals(ContentVersionInterface::ORIGIN_EDIT, $version->getOrigin());

        $version->setOrigin(ContentVersionInterface::ORIGIN_FIXTURE);

        $version->setOrigin(ContentVersionInterface::ORIGIN_IMPORT);
        $this->assertEquals(ContentVersionInterface::ORIGIN_IMPORT, $version->getOrigin());

        $version->setOrigin(ContentVersionInterface::ORIGIN_UNKNOWN);
        $this->assertEquals(ContentVersionInterface::ORIGIN_UNKNOWN, $version->getOrigin());

        $this->assertNull($version->getOriginDescription());
        $version->setOriginDescription('test');
        $this->assertEquals('test', $version->getOriginDescription());
    }

    public function testNote(): void
    {
        $version = new ContentVersion();
        $this->assertNull($version->getNote());

        $version->setNote('test');
        $this->assertEquals('test', $version->getNote());
    }

    public function testLayout(): void
    {
        $version = new ContentVersion();
        $this->assertNull($version->getLayout());

        $version->setLayout('test');
        $this->assertEquals('test', $version->getLayout());
    }

    public function testCreatedAt(): void
    {
        $version = new ContentVersion();
        $this->assertNull($version->getCreatedAt());

        $date = new \DateTime();
        $version->setCreatedAt($date);
        $this->assertEquals($date->format('Y-m-d'), $version->getCreatedAt()->format('Y-m-d'));

        $version->setCreatedAt(null);
        $version->autoSetCreatedAt();
        $this->assertEquals($date->format('Y-m-d'), $version->getCreatedAt()->format('Y-m-d'));
    }

    public function testVersionNumber(): void
    {
        $version = new ContentVersion();
        $this->assertNull($version->getVersionNumber());

        $version->setVersionNumber(1);
        $this->assertEquals(1, $version->getVersionNumber());
    }

    public function testData(): void
    {
        $version = new ContentVersion();
        $this->assertEmpty($version->getData());

        $version->setData(['test' => 'test']);
        $this->assertEquals(['test' => 'test'], $version->getData());

        $version->_setDataCallback(function ($data) {
            $data['test'] = 'test2';
            return $data;
        });
        $this->assertEquals(['test' => 'test2'], $version->getData());
    }

    public function testCompiled(): void
    {
        $version = new ContentVersion();
        $this->assertEmpty($version->getCompiled());

        $version->setCompiled(['es' => 'test']);
        $this->assertEquals(['es' => 'test'], $version->getCompiled());

        $version->setCompiledModules(['es' => 'test']);
        $this->assertEquals(['es' => 'test'], $version->getCompiledModules());

        $this->assertFalse($version->hasCompileErrors());

        $version->setCompiled(['es' => 'test with MODULE_RENDER_ERROR']);
        $this->assertTrue($version->hasCompileErrors());

        $version->setCompiled(['es' => ['test' => 'test with MODULE_RENDER_ERROR']]);
        $this->assertTrue($version->hasCompileErrors());
    }

    public function testPublished(): void
    {
        $version = new ContentVersion();
        $this->assertFalse($version->isPublished());

        $version->setContent($page = new Page());
        $page->setPublishedVersion(new ContentVersion());
        $this->assertFalse($version->isPublished());

        $page->setPublishedVersion($version);
        $this->assertTrue($version->isPublished());
    }

    public function testLastVersion(): void
    {
        $version = new ContentVersion();
        $this->assertFalse($version->isLastVersion());

        $page = new Page();
        $page->addVersion($latestVersion = new ContentVersion());
        $page->addVersion($version);
        $page->addVersion(new ContentVersion());
        $this->assertFalse($version->isLastVersion());

        $page->removeVersion($latestVersion);
        $this->assertTrue($version->isLastVersion());
    }

    public function testDeleteOnCleanup(): void
    {
        $page = new Page();
        $version = new ContentVersion();

        $version->setKeep(true);
        $this->assertTrue($version->isKeep());
        $this->assertFalse($version->deleteOnCleanup());

        $version->setKeep(false);
        $this->assertFalse($version->isKeep());
        $this->assertTrue($version->deleteOnCleanup());

        // check last version
        $page->addVersion($version);
        $this->assertFalse($version->deleteOnCleanup());

        // check published
        $page->removeVersion($version);
        $page->setPublishedVersion($version);
        $this->assertFalse($version->deleteOnCleanup());
    }

    public function testMedias(): void
    {
        $version = new ContentVersion();
        $this->assertEmpty($version->getMedias());

        $version->addMedia($media = new Media());
        $this->assertCount(1, $version->getMedias());

        $version->removeMedia($media);
        $this->assertEmpty($version->getMedias());
    }

    public function testRoutes(): void
    {
        $version = new ContentVersion();
        $this->assertEmpty($version->getRoutes());

        $version->addRoute($route = new Route());
        $this->assertCount(1, $version->getRoutes());

        $version->removeRoute($route);
        $this->assertEmpty($version->getRoutes());
    }
}