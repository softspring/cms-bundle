<?php

namespace Softspring\CmsBundle\Test\Unit\Config\EntityTransformer;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Entity\Block;
use Softspring\CmsBundle\Entity\Route;
use Softspring\CmsBundle\EntityTransformer\BlockTransformer;
use Softspring\CmsBundle\EntityTransformer\UnsupportedException;

class BlockTransformerTest extends TestCase
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

        $blockTransformer = new BlockTransformer();
        $blockTransformer->transform(new \stdClass(), $this->em);
    }

    public function testEmptyData(): void
    {
        $block = new Block();

        $blockTransformer = new BlockTransformer();

        $blockTransformer->transform($block, $this->em);
        $this->assertNull($block->getData());

        $blockTransformer->untransform($block, $this->em);
        $this->assertNull($block->getData());
    }
    
    public function testTransform(): void
    {
        $route = new Route();
        (new \ReflectionClass($route))->getProperty('id')->setValue($route, 'route_id');

        $block = new Block();
        $block->setData([
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

        $blockTransformer = new BlockTransformer();
        $blockTransformer->transform($block, $this->em);

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
        ], $block->getData());
    }

    public function testUntransform(): void
    {
        $route = new Route();
        (new \ReflectionClass($route))->getProperty('id')->setValue($route, 'route_id');

        $this->routeRepository->method('findOneBy')->willReturn($route);

        $block = new Block();
        $block->setData([
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

        $blockTransformer = new BlockTransformer();
        $blockTransformer->untransform($block, $this->em);

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
        ], $block->getData());
    }
}
