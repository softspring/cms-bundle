<?php

namespace Softspring\CmsBundle\Test\Unit\Config\Entity;

use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Entity\Page;
use Softspring\CmsBundle\Entity\Route;
use Softspring\CmsBundle\Entity\RoutePath;
use Softspring\CmsBundle\Entity\Site;
use Softspring\CmsBundle\Model\RouteInterface;

class RouteTest extends TestCase
{
    public function testIds(): void
    {
        $route = new Route();
        $this->assertNull($route->getId());

        $route->setId('test');
        $this->assertEquals('test', $route->getId());
        $this->assertEquals('test', "$route");
    }

    public function testTypes(): void
    {
        $route = new Route();
        $this->assertNull($route->getType());

        $route->setType(RouteInterface::TYPE_CONTENT);
        $this->assertEquals(RouteInterface::TYPE_CONTENT, $route->getType());
    }

    public function testSites(): void
    {
        $route = new Route();
        $route->addPath(new RoutePath());
        $this->assertCount(0, $route->getSites());

        $route->addSite($site = new Site());
        $site->setId('test');
        $this->assertCount(1, $route->getSites());

        $this->assertTrue($route->hasSite($site));

        $route->removeSite($site);
        $this->assertCount(0, $route->getSites());

        $this->assertFalse($route->hasSite($site));
    }

    public function testPaths(): void
    {
        $route = new Route();
        $this->assertCount(0, $route->getPaths());

        $route->addPath($path = new RoutePath());
        $this->assertCount(1, $route->getPaths());

        $route->removePath($path);
        $this->assertCount(0, $route->getPaths());
    }

    public function testChildren(): void
    {
        $route = new Route();
        $this->assertCount(0, $route->getChildren());

        $route->addChild($child = new Route());
        $this->assertCount(1, $route->getChildren());

        $route->removeChild($child);
        $this->assertCount(0, $route->getChildren());
    }

    public function testParent(): void
    {
        $route = new Route();
        $this->assertNull($route->getParent());

        $route->setParent($parent = new Route());
        $this->assertEquals($parent, $route->getParent());
    }

    public function testRedirectType(): void
    {
        $route = new Route();
        $this->assertNull($route->getRedirectType());

        $route->setRedirectType(301);
        $this->assertEquals(301, $route->getRedirectType());
    }

    public function testCompilePaths(): void
    {
        $route = new Route();
        $route->addPath($path = new RoutePath());
        $path->setPath('/test');
        $this->assertEquals('/test', $path->getPath());

        $route->compilePaths();
        $this->assertEquals('/test', $path->getPath());
    }

    public function testCompileChildrenPaths(): void
    {
        $route = new Route();
        $route->addChild($child = new Route());
        $child->addPath($path = new RoutePath());
        $path->setPath('/test');
        $this->assertEquals('/test', $path->getPath());

        $route->compileChildrenPaths();
        $this->assertEquals('/test', $path->getPath());
    }

    public function testContent(): void
    {
        $route = new Route();
        $this->assertNull($route->getContent());

        $route->setContent($content = new Page());
        $this->assertEquals($content, $route->getContent());
    }

    public function testRedirectUrl(): void
    {
        $route = new Route();
        $this->assertNull($route->getRedirectUrl());

        $route->setRedirectUrl('https://test.com');
        $this->assertEquals('https://test.com', $route->getRedirectUrl());
    }

    public function testSymfonyRoute(): void
    {
        $route = new Route();
        $this->assertNull($route->getSymfonyRoute());

        $route->setSymfonyRoute(['test']);
        $this->assertEquals(['test'], $route->getSymfonyRoute());
    }
}