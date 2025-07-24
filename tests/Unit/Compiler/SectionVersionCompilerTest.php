<?php

namespace Softspring\CmsBundle\Test\Unit\Compiler;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Compiler\CompileException;
use Softspring\CmsBundle\Compiler\SectionVersionCompiler;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Entity\ContentVersion;
use Softspring\CmsBundle\Entity\Section;
use Softspring\CmsBundle\Entity\SectionVersion;
use Softspring\CmsBundle\Helper\CmsHelper;
use Softspring\CmsBundle\Helper\CompileHelper;
use Softspring\CmsBundle\Manager\CompiledDataManagerInterface;
use Softspring\CmsBundle\Model\CompiledData;
use Softspring\CmsBundle\Model\SectionVersionInterface;
use Softspring\CmsBundle\Model\Site;
use Softspring\CmsBundle\Model\VersionInterface;
use Softspring\CmsBundle\Render\SectionVersionRenderer;
use Softspring\CmsBundle\Render\Error\RenderErrorList;
use Softspring\CmsBundle\Render\Exception\RenderException;
use Symfony\Component\HttpFoundation\Request;

class SectionVersionCompilerTest extends TestCase
{
    protected SectionVersionRenderer|MockObject $sectionVersionRenderMock;
    protected CompileHelper|MockObject $compileHelperMock;
    protected CmsHelper|MockObject $cmsHelperMock;
    protected CmsConfig|MockObject $cmsConfigMock;
    protected CompiledDataManagerInterface|MockObject $compiledDataManagerMock;
    protected SectionVersionCompiler $compiler;

    protected function setUp(): void
    {
        $this->sectionVersionRenderMock = $this->createMock(SectionVersionRenderer::class);
        $this->compileHelperMock = $this->createMock(CompileHelper::class);
        $this->cmsHelperMock = $this->createMock(CmsHelper::class);
        $this->cmsConfigMock = $this->createMock(CmsConfig::class);
        $this->compiledDataManagerMock = $this->createMock(CompiledDataManagerInterface::class);

        $this->compiler = new SectionVersionCompiler(
            $this->sectionVersionRenderMock,
            $this->compiledDataManagerMock,
            $this->cmsHelperMock,
            null // LoggerInterface can be null for tests
        );

        $this->cmsHelperMock->method('config')->willReturn($this->cmsConfigMock);

        $site1 = new Site(); $site1->setId('site1'); $site1->setConfig(['hosts'=>[]]);
        $site2 = new Site(); $site2->setId('site2'); $site2->setConfig(['hosts'=>[]]);
        $this->cmsConfigMock->method('getSites')->willReturn([$site1, $site2]);

        $this->cmsHelperMock->method('compile')
            ->willReturn($this->compileHelperMock);

        $this->compiledDataManagerMock->method('createEntity')
            ->willReturnCallback(function() {
                return new CompiledData();
            });

        $this->compiledDataManagerMock->method('getCompileKeyFromRequest')->willReturnCallback(function(VersionInterface $version, Request $request) {
            return sprintf('test_key/%s/%s', $request->getLocale(), $request->attributes->get('_sfs_cms_site'));
        });
    }

    public function testCompileRequestInvalidClassException(): void
    {
        $this->expectException(CompileException::class);
        $this->expectExceptionMessage('Version must be an instance of SectionVersionInterface');
        $this->compiler->compileRequest(new ContentVersion(), new Request());
    }

    public function testCompileRequestRenderErrors(): void
    {
        $this->sectionVersionRenderMock->method('render')->willReturnCallback(function (SectionVersionInterface $version, Request $request, ?RenderErrorList $renderErrorList = null) {
            $renderErrorList->add('test_template', new \Exception('Test error'), ['context' => 'data']);
            return 'rendered section';
        });

        $compiledData = $this->compiler->compileRequest($version = new SectionVersion(), new Request());

        $this->assertInstanceOf(CompiledData::class, $compiledData);
        $this->assertArrayHasKey('content', $compiledData->getData());
        $this->assertArrayHasKey('errors', $compiledData->getData());
        $this->assertTrue($compiledData->hasErrors());
        $this->assertTrue($version->hasCompileErrors());

        $this->assertEquals('test_template', $compiledData->getDataPart('errors')[0]['template']);
        $this->assertEquals('Test error', $compiledData->getDataPart('errors')[0]['exception']['message']);
        $this->assertEquals('data', $compiledData->getDataPart('errors')[0]['contextData']['context']);
        $this->assertEquals('rendered section', $compiledData->getDataPart('content'));
    }

    public function testCompileRequestWithRenderException(): void
    {
        $this->sectionVersionRenderMock->method('render')->willThrowException(new RenderException('Test error'));

        $compiledData = $this->compiler->compileRequest($version = new SectionVersion(), new Request());

        $this->assertInstanceOf(CompiledData::class, $compiledData);
        $this->assertArrayHasKey('content', $compiledData->getData());
        $this->assertArrayHasKey('errors', $compiledData->getData());
        $this->assertTrue($compiledData->hasErrors());
        $this->assertTrue($version->hasCompileErrors());

        $this->assertEquals(RenderException::class, $compiledData->getDataPart('errors')[0]['class']);
        $this->assertEquals('Test error', $compiledData->getDataPart('errors')[0]['message']);
    }

    public function testCompileRequestWithInvalidException(): void
    {
        $this->expectException(CompileException::class);
        $this->sectionVersionRenderMock->method('render')->willThrowException(new \Exception('Test error'));

        $this->compiler->compileRequest(new SectionVersion(), new Request());
    }

    public function testCompileAllRequestInvalidClassException(): void
    {
        $this->expectException(CompileException::class);
        $this->expectExceptionMessage('Version must be an instance of SectionVersionInterface');
        $this->compiler->compileAll(new ContentVersion());
    }

    public function testCompileAll(): void
    {
        $section = new Section();
        $section->setDefaultLocale('en');
        $section->addLocale('es');
        $sectionVersion = new SectionVersion();
        $sectionVersion->setSection($section);

        $compiledDatas = $this->compiler->compileAll($sectionVersion);
        $this->assertCount(4, $compiledDatas);
        $this->assertInstanceOf(CompiledData::class, $compiledDatas[0]);
        $this->assertInstanceOf(CompiledData::class, $compiledDatas[1]);
        $this->assertInstanceOf(CompiledData::class, $compiledDatas[2]);
        $this->assertInstanceOf(CompiledData::class, $compiledDatas[3]);
        $this->assertCount(0, $sectionVersion->getCompiled());
    }

    public function testCompileAllWithSaveOption(): void
    {
        $this->compileHelperMock->method('sectionSaveCompiled')->willReturn(true);

        $section = new Section();
        $section->setDefaultLocale('en');
        $section->addLocale('es');
        $sectionVersion = new SectionVersion();
        $sectionVersion->setSection($section);

        $compiledDatas = $this->compiler->compileAll($sectionVersion);
        $this->assertCount(4, $compiledDatas);
        $this->assertInstanceOf(CompiledData::class, $compiledDatas[0]);
        $this->assertInstanceOf(CompiledData::class, $compiledDatas[1]);
        $this->assertInstanceOf(CompiledData::class, $compiledDatas[2]);
        $this->assertInstanceOf(CompiledData::class, $compiledDatas[3]);
        $this->assertCount(4, $sectionVersion->getCompiled());
        $this->assertEquals($compiledDatas[0], $sectionVersion->getCompiled()->get(0));
        $this->assertEquals($compiledDatas[1], $sectionVersion->getCompiled()->get(1));
        $this->assertEquals($compiledDatas[2], $sectionVersion->getCompiled()->get(2));
        $this->assertEquals($compiledDatas[3], $sectionVersion->getCompiled()->get(3));
    }
}