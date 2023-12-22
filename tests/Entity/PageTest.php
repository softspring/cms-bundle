<?php

namespace Softspring\CmsBundle\Test\Config\Entity;

use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Entity\ContentVersion;
use Softspring\CmsBundle\Entity\Page;
use Softspring\CmsBundle\Entity\Route;
use Softspring\CmsBundle\Entity\Site;

class PageTest extends TestCase
{
    public function testIds(): void
    {
        $page = new Page();
        $this->assertNull($page->getId());

        $reflection = new \ReflectionClass($page);
        $property = $reflection->getProperty('id');
        $property->setValue($page, 'test');
        $this->assertEquals('test', $page->getId());
        $this->assertEquals('test', "$page");
    }

    public function testName(): void
    {
        $page = new Page();
        $this->assertNull($page->getName());

        $page->setName('test');
        $this->assertEquals('test', $page->getName());
    }

    public function testSites(): void
    {
        $page = new Page();
        $page->addRoute($route = new Route());

        $site1 = new Site();
        $site2 = new Site();

        $this->assertCount(0, $page->getSites());
        $page->addSite($site1);
        $page->addSite($site2);
        $this->assertCount(2, $page->getSites());

        $this->assertEquals([$site1, $site2], $page->getSitesSorted()->toArray());

        $site2->setConfig([
            'extra' => [
                'order' => 1,
            ],
        ]);
        $this->assertEquals([$site2, $site1], $page->getSitesSorted()->toArray());

        $page->removeSite($site1);
        $this->assertCount(1, $page->getSites());
    }

    public function testRoutes(): void
    {
        $page = new Page();
        $this->assertCount(0, $page->getRoutes());

        $page->addRoute($route = new Route());
        $this->assertCount(1, $page->getRoutes());

        $page->removeRoute($route);
        $this->assertCount(0, $page->getRoutes());
    }

    public function testExtraData(): void
    {
        $page = new Page();
        $this->assertNull($page->getExtraData());

        $page->setExtraData(['test']);
        $this->assertEquals(['test'], $page->getExtraData());
    }

    public function testSeo(): void
    {
        $page = new Page();
        $this->assertNull($page->getSeo());

        $page->setSeo(['test']);
        $this->assertEquals(['test'], $page->getSeo());
    }

    public function testVersions(): void
    {
        $page = new Page();
        $this->assertCount(0, $page->getVersions());

        $page->addVersion($version = new ContentVersion());
        $this->assertCount(1, $page->getVersions());

        $page->removeVersion($version);
        $this->assertCount(0, $page->getVersions());

        $this->assertNull($page->getLastVersion());
        $page->setLastVersion($version);
        $this->assertEquals($version, $page->getLastVersion());

        $this->assertNull($page->getPublishedVersion());
        $page->setPublishedVersion($version);
        $this->assertEquals($version, $page->getPublishedVersion());
    }

    public function testLastVersionNumber(): void
    {
        $page = new Page();
        $this->assertNull($page->getLastVersionNumber());

        $page->setLastVersionNumber(1);
        $this->assertEquals(1, $page->getLastVersionNumber());
    }
}