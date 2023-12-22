<?php

namespace Softspring\CmsBundle\Test\Config\EntityTransformer;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Entity\Content;
use Softspring\CmsBundle\Entity\Page;
use Softspring\CmsBundle\Entity\Route;
use Softspring\CmsBundle\EntityTransformer\ContentTransformer;
use Softspring\CmsBundle\EntityTransformer\UnsupportedException;

class ContentTransformerTest extends TestCase
{
    protected EntityManager|MockObject $em;
    protected ClassMetadata|MockObject $routeClassMetadata;
    protected EntityRepository|MockObject $routeRepository;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManager::class);

        $this->routeClassMetadata = $this->createMock(ClassMetadata::class);
        $this->routeClassMetadata->method('getIdentifierValues')->willReturnCallback(function (Route $route) {
            return ['id' => $route->getId()];
        });
        $this->em->method('getClassMetadata')->with(Route::class)->willReturn($this->routeClassMetadata);

        $this->routeRepository = $this->createMock(EntityRepository::class);
        $this->em->method('getRepository')->with(Route::class)->willReturn($this->routeRepository);
    }

    public function testUnsupported(): void
    {
        $this->expectException(UnsupportedException::class);

        $contentTransformer = new ContentTransformer();
        $contentTransformer->transform(new \stdClass(), $this->em);
    }

    public function testEmptyData(): void
    {
        $content = new Page();

        $contentTransformer = new ContentTransformer();

        $contentTransformer->transform($content, $this->em);
        $this->assertNull($content->getExtraData());

        $contentTransformer->untransform($content, $this->em);
        $this->assertNull($content->getExtraData());
    }
    
    public function testTransform(): void
    {
        $route = new Route();
        (new \ReflectionClass($route))->getProperty('id')->setValue($route, 'route_id');

        $content = new Page();
        $content->setExtraData([
            'test' => 'test',
            'test2' => [
                'test3' => 'test3',
            ],
            'test4' => [
                'test5' => [
                    'test6' => 'test6',
                ],
            ],
            'route' => $route,
        ]);

        $contentTransformer = new ContentTransformer();
        $contentTransformer->transform($content, $this->em);

        $this->assertEquals([
            'test' => 'test',
            'test2' => [
                'test3' => 'test3',
            ],
            'test4' => [
                'test5' => [
                    'test6' => 'test6',
                ],
            ],
            'route' => [
                '_entity_class' => Route::class,
                '_entity_id' => [
                    'id' => 'route_id',
                ],
            ],
        ], $content->getExtraData());
    }

    public function testUntransform(): void
    {
        $route = new Route();
        (new \ReflectionClass($route))->getProperty('id')->setValue($route, 'route_id');

        $this->routeRepository->method('findOneBy')->willReturn($route);

        $content = new Page();
        $content->setExtraData([
            'test' => 'test',
            'test2' => [
                'test3' => 'test3',
            ],
            'test4' => [
                'test5' => [
                    'test6' => 'test6',
                ],
            ],
            'route' => [
                '_entity_class' => Route::class,
                '_entity_id' => [
                    'id' => 'route_id',
                ],
            ],
        ]);

        $contentTransformer = new ContentTransformer();
        $contentTransformer->untransform($content, $this->em);

        $this->assertEquals([
            'test' => 'test',
            'test2' => [
                'test3' => 'test3',
            ],
            'test4' => [
                'test5' => [
                    'test6' => 'test6',
                ],
            ],
            'route' => $route,
        ], $content->getExtraData());
    }
}
