<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Test\Unit\Manager;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Compiler\ContentVersionCompiler;
use Softspring\CmsBundle\Entity\ContentVersion;
use Softspring\CmsBundle\Entity\Site;
use Softspring\CmsBundle\Helper\CmsHelper;
use Softspring\CmsBundle\Manager\CompiledDataManagerInterface;
use Softspring\CmsBundle\Manager\ContentVersionManager;

class ContentVersionManagerTest extends TestCase
{
    public function testAddLocaleUpdatesTranslationsAndNonEmptyLocaleFiltersRecursively(): void
    {
        $version = new ContentVersion();
        $version->setData([
            'main' => [
                [
                    '_module' => 'container',
                    '_revision' => 1,
                    'title' => [
                        '_trans_id' => 'title-1',
                        '_default' => 'en',
                        'en' => 'Hello',
                    ],
                    'locale_filter' => ['en' => true],
                    'modules' => [
                        [
                            '_module' => 'text',
                            'body' => [
                                '_trans_id' => 'body-1',
                                '_default' => 'en',
                                'en' => 'Body',
                            ],
                            'locale_filter' => [],
                        ],
                    ],
                ],
            ],
        ]);

        $this->createManager()->addLocale($version, 'es');

        self::assertSame([
            'main' => [
                [
                    '_module' => 'container',
                    '_revision' => 1,
                    'title' => [
                        '_trans_id' => 'title-1',
                        '_default' => 'en',
                        'en' => 'Hello',
                        'es' => null,
                    ],
                    'locale_filter' => ['en' => true, 'es' => true],
                    'modules' => [
                        [
                            '_module' => 'text',
                            'body' => [
                                '_trans_id' => 'body-1',
                                '_default' => 'en',
                                'en' => 'Body',
                                'es' => null,
                            ],
                            'locale_filter' => [],
                        ],
                    ],
                ],
            ],
        ], $version->getData());
    }

    public function testAddSiteUpdatesNonEmptySiteFiltersRecursively(): void
    {
        $site = new Site();
        $site->setId('site_es');

        $version = new ContentVersion();
        $version->setData([
            'main' => [
                [
                    '_module' => 'container',
                    'site_filter' => ['default' => true],
                    'modules' => [
                        [
                            '_module' => 'text',
                            'site_filter' => [],
                        ],
                        [
                            '_module' => 'gallery',
                            'site_filter' => ['default' => true],
                        ],
                    ],
                ],
            ],
        ]);

        $this->createManager()->addSite($version, $site);

        self::assertSame([
            'main' => [
                [
                    '_module' => 'container',
                    'site_filter' => ['default' => true, 'site_es' => true],
                    'modules' => [
                        [
                            '_module' => 'text',
                            'site_filter' => [],
                        ],
                        [
                            '_module' => 'gallery',
                            'site_filter' => ['default' => true, 'site_es' => true],
                        ],
                    ],
                ],
            ],
        ], $version->getData());
    }

    private function createManager(): ContentVersionManager
    {
        return new ContentVersionManager(
            $this->createStub(EntityManagerInterface::class),
            $this->createStub(CmsHelper::class),
            $this->createStub(ContentVersionCompiler::class),
            $this->createStub(CompiledDataManagerInterface::class),
            true
        );
    }
}
