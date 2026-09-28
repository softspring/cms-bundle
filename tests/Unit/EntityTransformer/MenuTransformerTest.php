<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Test\Unit\EntityTransformer;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Entity\Menu;
use Softspring\CmsBundle\Entity\Route;
use Softspring\CmsBundle\EntityTransformer\MenuTransformer;
use Softspring\CmsBundle\EntityTransformer\UnsupportedException;
use stdClass;

class MenuTransformerTest extends TestCase
{
    public function testUnsupportedEntityThrowsException(): void
    {
        $this->expectException(UnsupportedException::class);

        (new MenuTransformer())->transform(new stdClass(), $this->createStub(EntityManager::class));
    }

    public function testTransformReturnsEarlyWhenMenuHasNoData(): void
    {
        $menu = new Menu();

        (new MenuTransformer())->transform($menu, $this->createStub(EntityManager::class));

        self::assertNull($menu->getData());
    }

    public function testTransformSerializesEntityValuesInsideMenuData(): void
    {
        $route = new Route();
        $route->setId('home');

        $metadata = $this->createStub(ClassMetadata::class);
        $metadata->method('getIdentifierValues')->willReturn(['id' => 'home']);

        $em = $this->createMock(EntityManager::class);
        $em->method('getClassMetadata')->with(Route::class)->willReturn($metadata);

        $menu = new Menu();
        $menu->setData([
            'cta' => [
                'route' => $route,
            ],
        ]);

        (new MenuTransformer())->transform($menu, $em);

        self::assertSame([
            'cta' => [
                'route' => [
                    '_entity_class' => Route::class,
                    '_entity_id' => ['id' => 'home'],
                ],
            ],
        ], $menu->getData());
    }

    public function testUntransformRestoresEntityReferencesAndCachesRepeatedLookups(): void
    {
        $route = new Route();
        $route->setId('home');

        $repository = $this->createMock(EntityRepository::class);
        $repository->expects($this->once())
            ->method('findOneBy')
            ->with(['id' => 'home'])
            ->willReturn($route);

        $em = $this->createMock(EntityManager::class);
        $em->method('getRepository')->with(Route::class)->willReturn($repository);

        $serializedRoute = [
            '_entity_class' => Route::class,
            '_entity_id' => ['id' => 'home'],
        ];
        $menu = new Menu();
        $menu->setData([
            'primary' => $serializedRoute,
            'nested' => [
                'again' => $serializedRoute,
            ],
        ]);

        (new MenuTransformer())->untransform($menu, $em);

        self::assertSame($route, $menu->getData()['primary']);
        self::assertSame($route, $menu->getData()['nested']['again']);
    }
}
