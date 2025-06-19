<?php

namespace Softspring\CmsBundle\Test\Unit\Render;

use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Form\Module\ContainerModuleType;
use Softspring\CmsBundle\Form\Module\DynamicFormModuleType;
use Softspring\CmsBundle\Model\Site;
use Softspring\CmsBundle\Render\Isolated\IsolatedRequest;
use Softspring\CmsBundle\Render\ModuleRenderer;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;

class ModuleRendererTest extends TestCase
{
    protected CmsConfig|MockObject $cmsConfig;
    protected RequestStack|MockObject $requestStack;
    protected Environment|MockObject $twig;

    protected function setUp(): void
    {
        $this->cmsConfig = $this->createMock(CmsConfig::class);
        $this->requestStack = $this->createMock(RequestStack::class);
        $this->twig = $this->createMock(Environment::class);
    }

    public function testSkipBySiteFilter(): void
    {
        $site1 = new Site();
        $site1->setId('site_1');
        $site1->setConfig([
            'hosts' => [
                ['domain' => 'site1.example.com', 'canonical' => true],
            ],
        ]);

        $site2 = new Site();
        $site2->setId('site_2');
        $site2->setConfig([
            'hosts' => [
                ['domain' => 'site2.example.com', 'canonical' => true],
            ],
        ]);

        $this->cmsConfig->expects($this->any())
            ->method('getSite')
            ->willReturnCallback(function ($site) use ($site1, $site2) {
                return match ($site) {
                    'site_1' => $site1,
                    'site_2' => $site2,
                    default => null,
                };
            });

        $currentRequest = IsolatedRequest::createIsolated('en', $site1);
        $this->requestStack->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn($currentRequest);

        $moduleRenderer = new ModuleRenderer($this->cmsConfig, $this->requestStack, $this->twig, null);

        $profilerDebugCollectorData = [];
        $return = $moduleRenderer->render([
            '_module' => 'test_module',
            'site_filter' => [
                'site_2',
            ],
        ], $profilerDebugCollectorData, [], null);

        $this->assertEquals(ModuleRenderer::SITE_HIDDEN_MODULE."\n", $return);
    }

    public function testSkipByLocaleFilter(): void
    {
        $site = new Site();
        $site->setId('site_1');
        $site->setConfig([
            'hosts' => [
                ['domain' => 'site1.example.com', 'canonical' => true],
            ],
        ]);

        $this->cmsConfig->expects($this->any())
            ->method('getSite')
            ->willReturn($site);

        $this->cmsConfig->expects($this->any())
            ->method('getModule')
            ->willReturn([
                'revision_migration_scripts' => [],
                'revision' => 1,
                'module_type' => DynamicFormModuleType::class,
                'render_template' => '',
            ]);

        $currentRequest = IsolatedRequest::createIsolated('fr', $site);
        $this->requestStack->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn($currentRequest);

        $moduleRenderer = new ModuleRenderer($this->cmsConfig, $this->requestStack, $this->twig, null);

        $profilerDebugCollectorData = [];
        $return = $moduleRenderer->render([
            '_module' => 'test_module',
            'locale_filter' => [
                'en',
            ],
        ], $profilerDebugCollectorData, [], null);

        $this->assertEquals(ModuleRenderer::LOCALE_HIDDEN_MODULE."\n", $return);
    }

    public function testRenderNoContainerModule(): void
    {
        $site = new Site();
        $site->setId('site_1');
        $site->setConfig([
            'hosts' => [
                ['domain' => 'site1.example.com', 'canonical' => true],
            ],
        ]);

        $this->cmsConfig->expects($this->any())
            ->method('getSite')
            ->willReturn($site);

        $this->cmsConfig->expects($this->any())
            ->method('getModule')
            ->willReturn([
                'revision' => 1,
                'revision_migration_scripts' => [],
                'container' => false,
                'render_template' => 'test_module.html.twig',
                'module_type' => DynamicFormModuleType::class,
            ]);

        $this->twig->expects($this->once())
            ->method('render')
            ->with('test_module.html.twig')
            ->willReturn('Test content');

        $moduleRenderer = new ModuleRenderer($this->cmsConfig, $this->requestStack, $this->twig, null);

        $profilerDebugCollectorData = [];
        $return = $moduleRenderer->render([
            '_module' => 'test_module',
            'content' => 'Test content',
        ], $profilerDebugCollectorData, [], null);

        // Assuming the module is rendered as a string
        $this->assertStringContainsString('Test content', $return);
    }

    public function testContainerModule(): void
    {
        $site = new Site();
        $site->setId('site_1');
        $site->setConfig([
            'hosts' => [
                ['domain' => 'site1.example.com', 'canonical' => true],
            ],
        ]);

        $this->cmsConfig->expects($this->any())
            ->method('getSite')
            ->willReturn($site);

        $this->cmsConfig->expects($this->any())
            ->method('getModule')
            ->willReturnCallback(function ($module) {
                return match ($module) {
                    'test_container_module' => [
                        'revision' => 1,
                        'revision_migration_scripts' => [],
                        'container' => true,
                        'render_template' => 'test_container_module.html.twig',
                        'module_type' => ContainerModuleType::class,
                    ],
                    'test_module' => [
                        'revision' => 1,
                        'revision_migration_scripts' => [],
                        'container' => false,
                        'render_template' => 'test_module.html.twig',
                        'module_type' => DynamicFormModuleType::class,
                    ],
                    default => null,
                };
            });

        $this->twig->expects($this->any())
            ->method('render')
            ->willReturnCallback(function (string $template, array $data) {
                if ('test_container_module.html.twig' === $template) {
                    // Simulate rendering of a container module
                    $content = '';
                    foreach ($data['contents'] as $submodule) {
                        $content .= $submodule ?? '';
                    }

                    return $content;
                } elseif ('test_module.html.twig' === $template) {
                    // Simulate rendering of a simple module
                    return $data['content'] ?? '';
                } else {
                    throw new Exception("Unknown template: $template");
                }
            });

        $moduleRenderer = new ModuleRenderer($this->cmsConfig, $this->requestStack, $this->twig, null);

        try {
            $profilerDebugCollectorData = [];
            $return = $moduleRenderer->render([
                '_module' => 'test_container_module',
                'modules' => [
                    [
                        '_module' => 'test_module',
                        'content' => 'Test content 1',
                    ],
                    [
                        '_module' => 'test_module',
                        'content' => 'Test content 2',
                    ],
                ],
            ], $profilerDebugCollectorData, [], null);

            // Assuming the module is rendered as a string
            $this->assertStringContainsString('Test content', $return);
        } catch (Exception $e) {
            $this->fail('Rendering failed with exception: '.$e->getMessage());
        }
    }
}
