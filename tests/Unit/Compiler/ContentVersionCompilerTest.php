<?php

namespace Softspring\CmsBundle\Test\Unit\Compiler;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Compiler\CompileException;
use Softspring\CmsBundle\Compiler\ContentVersionCompiler;
use Softspring\CmsBundle\Entity\ContentVersion;
use Softspring\CmsBundle\Entity\Page;
use Softspring\CmsBundle\Entity\SectionVersion;
use Softspring\CmsBundle\Helper\CmsHelper;
use Softspring\CmsBundle\Helper\CompileHelper;
use Softspring\CmsBundle\Manager\CompiledDataManagerInterface;
use Softspring\CmsBundle\Model\CompiledData;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\Model\Site;
use Softspring\CmsBundle\Model\VersionInterface;
use Softspring\CmsBundle\Render\ContentVersionRenderer;
use Softspring\CmsBundle\Render\Error\RenderErrorList;
use Softspring\CmsBundle\Render\Exception\RenderException;
use Symfony\Component\HttpFoundation\Request;

class ContentVersionCompilerTest extends TestCase
{
    protected ContentVersionRenderer|MockObject $contentVersionRenderMock;
    protected CompileHelper|MockObject $compileHelperMock;
    protected CmsHelper|MockObject $cmsHelperMock;
    protected CompiledDataManagerInterface|MockObject $compiledDataManagerMock;
    protected ContentVersionCompiler $compiler;

    protected function setUp(): void
    {
        $this->contentVersionRenderMock = $this->createMock(ContentVersionRenderer::class);
        $this->compileHelperMock = $this->createMock(CompileHelper::class);
        $this->cmsHelperMock = $this->createMock(CmsHelper::class);
        $this->compiledDataManagerMock = $this->createMock(CompiledDataManagerInterface::class);

        $this->compiler = new ContentVersionCompiler(
            $this->contentVersionRenderMock,
            $this->compiledDataManagerMock,
            $this->cmsHelperMock,
            null // LoggerInterface can be null for tests
        );

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
        $this->expectExceptionMessage('Version must be an instance of ContentVersionInterface');
        $this->compiler->compileRequest(new SectionVersion(), new Request());
    }

    public function testCompileRequestPreviousContainers(): void
    {
        $preCompiledData = new CompiledData();
        $preCompiledData->setDataPart('containers', ['container1' => ['data' => 'value1']]);

        $version = new ContentVersion();
        $request = new Request();
        $compiledData = $this->compiler->compileRequest($version, $request, $preCompiledData);

        $this->assertInstanceOf(CompiledData::class, $compiledData);
        $this->assertArrayHasKey('content', $compiledData->getData());
        $this->assertArrayNotHasKey('errors', $compiledData->getData());
    }

    public function testCompileRequestRenderErrors(): void
    {
        $this->contentVersionRenderMock->method('render')->willReturnCallback(function (ContentVersionInterface $version, Request $request, ?RenderErrorList $renderErrorList = null, ?array $compiledContainers = null) {
            $renderErrorList->add('test_template', new \Exception('Test error'), ['context' => 'data']);
            return 'rendered content';
        });

        $compiledData = $this->compiler->compileRequest($version = new ContentVersion(), new Request());

        $this->assertInstanceOf(CompiledData::class, $compiledData);
        $this->assertArrayHasKey('content', $compiledData->getData());
        $this->assertArrayHasKey('errors', $compiledData->getData());
        $this->assertTrue($compiledData->hasErrors());
        $this->assertTrue($version->hasCompileErrors());

        $this->assertEquals('test_template', $compiledData->getDataPart('errors')[0]['template']);
        $this->assertEquals('Test error', $compiledData->getDataPart('errors')[0]['exception']['message']);
        $this->assertEquals('data', $compiledData->getDataPart('errors')[0]['contextData']['context']);
        $this->assertEquals('rendered content', $compiledData->getDataPart('content'));
    }

    public function testCompileRequestWithRenderException(): void
    {
        $this->contentVersionRenderMock->method('render')->willThrowException(new RenderException('Test error'));

        $compiledData = $this->compiler->compileRequest($version = new ContentVersion(), new Request());

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
        $this->contentVersionRenderMock->method('render')->willThrowException(new \Exception('Test error'));

        $this->compiler->compileRequest(new ContentVersion(), new Request());
    }

    public function testCompileRequestContainersRenderError(): void
    {
        $this->contentVersionRenderMock->method('renderContainers')->willReturnCallback(function (ContentVersionInterface $version, Request $request, ?RenderErrorList $renderErrorList = null) {
            $renderErrorList->add('test_template', new \Exception('Test error'), ['context' => 'data']);
            return [];
        });

        $compiledData = $this->compiler->compileRequest($version = new ContentVersion(), new Request());

        $this->assertInstanceOf(CompiledData::class, $compiledData);
        // $this->assertArrayHasKey('containers', $compiledData->getData());
        $this->assertArrayHasKey('containers_errors', $compiledData->getData());
        $this->assertTrue($compiledData->hasErrors());
        $this->assertTrue($version->hasCompileErrors());
    }

    public function testCompileRequestContainersRenderException(): void
    {
        $this->contentVersionRenderMock->method('renderContainers')->willThrowException(new RenderException('Test error'));

        $compiledData = $this->compiler->compileRequest($version = new ContentVersion(), new Request());

        $this->assertInstanceOf(CompiledData::class, $compiledData);
        // $this->assertArrayHasKey('containers', $compiledData->getData());
        $this->assertArrayHasKey('containers_errors', $compiledData->getData());
        $this->assertTrue($compiledData->hasErrors());
        $this->assertTrue($version->hasCompileErrors());

        $this->assertEquals(RenderException::class, $compiledData->getDataPart('containers_errors')[0]['class']);
        $this->assertEquals('Test error', $compiledData->getDataPart('containers_errors')[0]['message']);
    }

    public function testCompileRequestContainersInvalidException(): void
    {
        $this->expectException(CompileException::class);
        $this->contentVersionRenderMock->method('renderContainers')->willThrowException(new \Exception('Test error'));

        $this->compiler->compileRequest(new ContentVersion(), new Request());
    }

    public function testCompileAllRequestInvalidClassException(): void
    {
        $this->expectException(CompileException::class);
        $this->expectExceptionMessage('Version must be an instance of ContentVersionInterface');
        $this->compiler->compileAll(new SectionVersion());
    }

    public function testCompileAll(): void
    {
        $page = new Page();
        $page->addSite($site1 = new Site()); $site1->setId('site1'); $site1->setConfig(['hosts'=>[]]);
        $page->addSite($site2 = new Site()); $site2->setId('site2'); $site2->setConfig(['hosts'=>[]]);
        $page->setDefaultLocale('en');
        $page->addLocale('es');
        $contentVersion = new ContentVersion();
        $contentVersion->setContent($page);

        $compiledDatas = $this->compiler->compileAll($contentVersion);
        $this->assertCount(4, $compiledDatas);
        $this->assertInstanceOf(CompiledData::class, $compiledDatas[0]);
        $this->assertInstanceOf(CompiledData::class, $compiledDatas[1]);
        $this->assertInstanceOf(CompiledData::class, $compiledDatas[2]);
        $this->assertInstanceOf(CompiledData::class, $compiledDatas[3]);
        $this->assertCount(0, $contentVersion->getCompiled());
    }

    public function testCompileAllWithSaveOption(): void
    {
        $this->compileHelperMock->method('contentSaveCompiled')->willReturn(true);

        $page = new Page();
        $page->addSite($site1 = new Site()); $site1->setId('site1'); $site1->setConfig(['hosts'=>[]]);
        $page->addSite($site2 = new Site()); $site2->setId('site2'); $site2->setConfig(['hosts'=>[]]);
        $page->setDefaultLocale('en');
        $page->addLocale('es');
        $contentVersion = new ContentVersion();
        $contentVersion->setContent($page);

        $compiledDatas = $this->compiler->compileAll($contentVersion);
        $this->assertCount(4, $compiledDatas);
        $this->assertInstanceOf(CompiledData::class, $compiledDatas[0]);
        $this->assertInstanceOf(CompiledData::class, $compiledDatas[1]);
        $this->assertInstanceOf(CompiledData::class, $compiledDatas[2]);
        $this->assertInstanceOf(CompiledData::class, $compiledDatas[3]);
        $this->assertCount(4, $contentVersion->getCompiled());
        $this->assertEquals($compiledDatas[0], $contentVersion->getCompiled()->get(0));
        $this->assertEquals($compiledDatas[1], $contentVersion->getCompiled()->get(1));
        $this->assertEquals($compiledDatas[2], $contentVersion->getCompiled()->get(2));
        $this->assertEquals($compiledDatas[3], $contentVersion->getCompiled()->get(3));
    }
}