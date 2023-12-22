<?php

namespace Softspring\CmsBundle\Test\Config\EntityTransformer;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Entity\ContentVersion;
use Softspring\CmsBundle\Entity\Route;
use Softspring\CmsBundle\EntityTransformer\ContentVersionTransformer;
use Softspring\CmsBundle\EntityTransformer\UnsupportedException;
use Softspring\MediaBundle\Entity\Media;

class ContentVersionTransformerTest extends TestCase
{
    protected EntityManager|MockObject $em;
    protected ClassMetadata|MockObject $routeClassMetadata;
    protected ClassMetadata|MockObject $mediaClassMetadata;
    protected EntityRepository|MockObject $routeRepository;
    protected EntityRepository|MockObject $mediaRepository;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManager::class);

        $this->routeClassMetadata = $this->createMock(ClassMetadata::class);
        $this->routeClassMetadata->method('getIdentifierValues')->willReturnCallback(function (Route $route) {
            return ['id' => $route->getId()];
        });
        $this->routeRepository = $this->createMock(EntityRepository::class);

        $this->mediaClassMetadata = $this->createMock(ClassMetadata::class);
        $this->mediaClassMetadata->method('getIdentifierValues')->willReturnCallback(function (Media $media) {
            return ['id' => $media->getId()];
        });
        $this->mediaRepository = $this->createMock(EntityRepository::class);

        $this->em->method('getClassMetadata')->willReturnCallback(
            function (string $class) {
                switch ($class) {
                    case Route::class:
                        return $this->routeClassMetadata;
                    case Media::class:
                        return $this->mediaClassMetadata;
                }
            }
        );

        $this->em->method('getRepository')->willReturnCallback(
            function (string $class) {
                switch ($class) {
                    case Route::class:
                        return $this->routeRepository;
                    case Media::class:
                        return $this->mediaRepository;
                }
            }
        );
    }

    public function testUnsupported(): void
    {
        $this->expectException(UnsupportedException::class);

        $versionTransformer = new ContentVersionTransformer();
        $versionTransformer->transform(new \stdClass(), $this->em);
    }

    public function testEmptyData(): void
    {
        $version = new ContentVersion();

        $versionTransformer = new ContentVersionTransformer();

        $versionTransformer->transform($version, $this->em);
        $this->assertNull($version->getData());

        $versionTransformer->untransform($version, $this->em);
        $this->assertNull($version->getData());
    }

    public function testTransform(): void
    {
        $route = new Route();
        (new \ReflectionClass($route))->getProperty('id')->setValue($route, 'route_id');

        $media = new Media();
        (new \ReflectionClass($media))->getProperty('id')->setValue($media, 'media_id');

        $data = [
            'header' => [
                [
                    '_module' => 'html',
                    'revision' => 1,
                    'field1' => 'value1',
                ]
            ],
            'container' => [
                [
                    '_module' => 'text',
                    'revision' => 1,
                    'text' => 'example',
                ],
                [
                    '_module' => 'container',
                    'revision' => 1,
                    'modules' => [
                        [
                            '_module' => 'text',
                            'revision' => 1,
                            'text' => 'example',
                        ],
                        [
                            '_module' => 'route',
                            'revision' => 1,
                            'route' => $route,
                        ],
                        [
                            '_module' => 'media',
                            'revision' => 1,
                            'media' => $media,
                        ],
                    ]
                ],
            ],
        ];
        $expected = [
            'header' => [
                [
                    '_module' => 'html',
                    'revision' => 1,
                    'field1' => 'value1',
                ]
            ],
            'container' => [
                [
                    '_module' => 'text',
                    'revision' => 1,
                    'text' => 'example',
                ],
                [
                    '_module' => 'container',
                    'revision' => 1,
                    'modules' => [
                        [
                            '_module' => 'text',
                            'revision' => 1,
                            'text' => 'example',
                        ],
                        [
                            '_module' => 'route',
                            'revision' => 1,
                            'route' => [
                                '_entity_class' => Route::class,
                                '_entity_id' => [
                                    'id' => 'route_id',
                                ],
                            ],
                        ],
                        [
                            '_module' => 'media',
                            'revision' => 1,
                            'media' => [
                                '_entity_class' => Media::class,
                                '_entity_id' => [
                                    'id' => 'media_id',
                                ],
                            ],
                        ],
                    ]
                ],
            ],
        ];

        $version = new ContentVersion();
        $version->setData($data);

        $versionTransformer = new ContentVersionTransformer();
        $versionTransformer->transform($version, $this->em);

        $this->assertEquals($expected, $version->getData());
    }

    public function testUntransform(): void
    {
        $route = new Route();
        (new \ReflectionClass($route))->getProperty('id')->setValue($route, 'route_id');
        $this->routeRepository->method('findOneBy')->willReturn($route);

        $media = new Media();
        (new \ReflectionClass($media))->getProperty('id')->setValue($media, 'media_id');
        $this->mediaRepository->method('findOneBy')->willReturn($media);

        $data = [
            'header' => [
                [
                    '_module' => 'html',
                    'revision' => 1,
                    'field1' => 'value1',
                ]
            ],
            'container' => [
                [
                    '_module' => 'text',
                    'revision' => 1,
                    'text' => 'example',
                ],
                [
                    '_module' => 'container',
                    'revision' => 1,
                    'modules' => [
                        [
                            '_module' => 'text',
                            'revision' => 1,
                            'text' => 'example',
                        ],
                        [
                            '_module' => 'route',
                            'revision' => 1,
                            'route' => [
                                '_entity_class' => Route::class,
                                '_entity_id' => [
                                    'id' => 'route_id',
                                ],
                            ],
                        ],
                        [
                            '_module' => 'media',
                            'revision' => 1,
                            'media' => [
                                '_entity_class' => Media::class,
                                '_entity_id' => [
                                    'id' => 'media_id',
                                ],
                            ],
                        ],
                    ]
                ],
            ],
        ];
        $expected = [
            'header' => [
                [
                    '_module' => 'html',
                    'revision' => 1,
                    'field1' => 'value1',
                ]
            ],
            'container' => [
                [
                    '_module' => 'text',
                    'revision' => 1,
                    'text' => 'example',
                ],
                [
                    '_module' => 'container',
                    'revision' => 1,
                    'modules' => [
                        [
                            '_module' => 'text',
                            'revision' => 1,
                            'text' => 'example',
                        ],
                        [
                            '_module' => 'route',
                            'revision' => 1,
                            'route' => $route,
                        ],
                        [
                            '_module' => 'media',
                            'revision' => 1,
                            'media' => $media,
                        ],
                    ]
                ],
            ],
        ];

        $version = new ContentVersion();
        $version->setData($data);

        $versionTransformer = new ContentVersionTransformer();
        $versionTransformer->untransform($version, $this->em);

        $this->assertEquals($expected, $version->getData());
    }
}
