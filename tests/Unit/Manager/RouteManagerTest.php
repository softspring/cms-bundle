<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Test\Unit\Manager;

use ReflectionClass;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Entity\Route;
use Softspring\CmsBundle\Entity\RoutePath;
use Softspring\CmsBundle\Manager\RouteManager;
use Softspring\CmsBundle\Manager\RoutePathManagerInterface;
use Softspring\CmsBundle\Model\RouteInterface;

class RouteManagerTest extends TestCase
{
    public function testCreateEntityAddsAnEmptyPathByDefault(): void
    {
        $route = $this->createManager()->createEntity();

        self::assertInstanceOf(Route::class, $route);
        self::assertCount(1, $route->getPaths());
        self::assertSame($route, $route->getPaths()->first()->getRoute());
    }

    public function testCreateEntityCanSkipInitialPath(): void
    {
        $route = $this->createManager()->createEntity(false);

        self::assertInstanceOf(Route::class, $route);
        self::assertCount(0, $route->getPaths());
    }

    public function testDuplicateEntityCopiesRouteDataAndDelegatesPathDuplication(): void
    {
        $path = new RoutePath();
        $path->setPath('about');

        $duplicatedPath = new RoutePath();
        $duplicatedPath->setPath('about-copy');

        $route = new Route();
        $route->setId('about');
        $route->setSymfonyRoute(['name' => 'app_about']);
        $route->setType(RouteInterface::TYPE_REDIRECT_TO_URL);
        $route->setRedirectType(301);
        $route->setRedirectUrl('/new-about');
        $route->addPath($path);

        $routePathManager = $this->createMock(RoutePathManagerInterface::class);
        $routePathManager->expects($this->once())
            ->method('duplicateEntity')
            ->with($path, 'copy')
            ->willReturn($duplicatedPath);

        $duplicate = $this->createManager($routePathManager)->duplicateEntity($route, null, 'copy');

        self::assertNotSame($route, $duplicate);
        self::assertSame('about_copy', $duplicate->getId());
        self::assertSame(['name' => 'app_about'], $duplicate->getSymfonyRoute());
        self::assertSame(RouteInterface::TYPE_REDIRECT_TO_URL, $duplicate->getType());
        self::assertSame(301, $duplicate->getRedirectType());
        self::assertSame('/new-about', $duplicate->getRedirectUrl());
        self::assertCount(1, $duplicate->getPaths());
        self::assertSame($duplicatedPath, $duplicate->getPaths()->first());
        self::assertSame($duplicate, $duplicatedPath->getRoute());
    }

    public function testItExposesRoutePathManager(): void
    {
        $routePathManager = $this->createStub(RoutePathManagerInterface::class);

        self::assertSame($routePathManager, $this->createManager($routePathManager)->getRoutePathManager());
    }

    private function createManager(?RoutePathManagerInterface $routePathManager = null): RouteManager
    {
        $metadata = $this->createStub(ClassMetadata::class);
        $metadata->method('getReflectionClass')->willReturn(new ReflectionClass(Route::class));

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getClassMetadata')
            ->with(RouteInterface::class)
            ->willReturn($metadata);

        return new RouteManager(
            $em,
            $routePathManager ?? $this->createStub(RoutePathManagerInterface::class)
        );
    }
}
