<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Test\Unit\Manager;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Compiler\ContentVersionCompiler;
use Softspring\CmsBundle\Entity\CompiledData;
use Softspring\CmsBundle\Entity\ContentVersion;
use Softspring\CmsBundle\Entity\Site;
use Softspring\CmsBundle\Helper\CmsHelper;
use Softspring\CmsBundle\Manager\CompiledDataManagerInterface;
use Softspring\CmsBundle\Manager\ContentVersionManager;
use Symfony\Component\HttpFoundation\Request;

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

    public function testGetCompiledContentDoesNotSaveQueryDependentCompiledData(): void
    {
        $version = new ContentVersion();
        $compiledData = new CompiledData();
        $compiledData->setDataPart('content', '<html>Page 3</html>');

        $contentCompiler = $this->createMock(ContentVersionCompiler::class);
        $contentCompiler->expects(self::once())
            ->method('compileRequest')
            ->with($version, self::isInstanceOf(Request::class), null, false)
            ->willReturn($compiledData);

        $compiledDataManager = $this->createMock(CompiledDataManagerInterface::class);
        $compiledDataManager->expects(self::never())->method('getRepository');

        $manager = $this->createManager($contentCompiler, $compiledDataManager);

        self::assertSame($compiledData, $manager->getCompiledContent($version, Request::create('/blog?page=3')));
        self::assertCount(0, $version->getCompiled());
    }

    private function createManager(?ContentVersionCompiler $contentCompiler = null, ?CompiledDataManagerInterface $compiledDataManager = null): ContentVersionManager
    {
        return new ContentVersionManager(
            $this->createStub(EntityManagerInterface::class),
            $this->createStub(CmsHelper::class),
            $contentCompiler ?? $this->createStub(ContentVersionCompiler::class),
            $compiledDataManager ?? $this->createStub(CompiledDataManagerInterface::class),
            true
        );
    }
}
