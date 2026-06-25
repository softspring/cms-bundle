<?php

namespace Softspring\CmsBundle\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Config\Model\Module;
use Softspring\CmsBundle\Entity\Page;
use Softspring\CmsBundle\Form\Extension\DefaultValueExtension;
use Softspring\CmsBundle\Form\Extension\DynamicTypesExtension;
use Softspring\CmsBundle\Form\Module\DynamicFormModuleType;
use Softspring\CmsBundle\Form\Type\LinkType;
use Softspring\CmsBundle\Form\Type\SymfonyRouteType;
use Softspring\CmsBundle\Form\Type\TranslatableType;
use Softspring\CmsBundle\Form\Type\TranslationType;
use Softspring\CmsBundle\Helper\CmsHelper;
use Softspring\CmsBundle\Manager\RouteManagerInterface;
use Softspring\CmsBundle\Render\Error\RenderErrorList;
use Softspring\CmsBundle\Render\Exception\ModuleRenderException;
use Softspring\CmsBundle\Render\Module\ModuleRenderer;
use Softspring\CmsBundle\Repository\RouteRepository;
use Softspring\CmsBundle\Translator\TranslatableContext;
use Softspring\CmsBundle\Utils\DataMigrator;
use Softspring\Component\DynamicFormType\Form\Extension\DynamicFormExtension;
use Softspring\Component\DynamicFormType\Form\Resolver\ConstraintResolver;
use Softspring\TranslatableBundle\Form\Extension\TranslationExtension;
use Softspring\TranslatableBundle\Form\Type\TranslatableType as BaseTranslatableType;
use Softspring\TranslatableBundle\Form\Type\TranslationType as BaseTranslationType;
use Softspring\TranslatableBundle\Model\Translation;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\Traits\ValidatorExtensionTrait;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Yaml\Yaml;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\TwigFilter;
use Twig\TwigFunction;

abstract class ModuleTestCase extends TypeTestCase
{
    use ValidatorExtensionTrait;

    protected string $moduleName = '';
    protected string $modulePath = '';
    protected string $defaultLocale = 'en';
    protected array $enabledLocales = ['es', 'en'];

    /**
     * @throws \Exception
     * @throws Exception
     */
    protected function getExtensions(): array
    {
        $cmsTypeResolver = new CmsModuleTypeResolver();

        $router = $this->createMock(RouterInterface::class);
        $router->method('getRouteCollection')->willReturn(new RouteCollection());

        $routeRepository = $this->createMock(RouteRepository::class);
        $routeRepository->method('getAllRouteIds')->willReturn([]);

        $routeManager = $this->createMock(RouteManagerInterface::class);
        $routeManager->method('getRepository')->willReturn($routeRepository);

        $cmsHelper = $this->createMock(CmsHelper::class);

        $translatableContext = new TranslatableContext($this->enabledLocales, $this->defaultLocale);

        $preloadedFormTypes = [];
        $preloadedFormTypes[] = new DynamicFormModuleType($cmsHelper);
        $preloadedFormTypes[] = new TranslatableType($translatableContext, $cmsTypeResolver);
        $preloadedFormTypes[] = new TranslationType($translatableContext, $cmsTypeResolver);
        $preloadedFormTypes[] = new BaseTranslatableType(null, null, $cmsTypeResolver);
        $preloadedFormTypes[] = new BaseTranslationType();
        $preloadedFormTypes[] = new SymfonyRouteType($router, $routeManager, []);
        $preloadedFormTypes[] = new LinkType($router, $routeManager, []);

        return [
            $this->getValidatorExtension(),
            new DynamicFormExtension($cmsTypeResolver, new ConstraintResolver()),
            new PreloadedExtension($preloadedFormTypes, [DynamicFormModuleType::class => [new DynamicTypesExtension($cmsTypeResolver)]]),
        ] +
            (class_exists(TranslationExtension::class) ? [new TranslationExtension($router, false)] : [])
        ;
    }

    protected function getTypeExtensions(): array
    {
        return [
            new DefaultValueExtension(),
        ];
    }

    protected function assertMigrateTest(array $originData, array $expectedData): void
    {
        $migratedData = DataMigrator::migrate(["$this->modulePath/migrate.php"], $originData, $expectedData['_revision']);
        $this->assertEquals($expectedData, $migratedData);
    }

    protected function provideDataForMigrations(): array
    {
        return [];
    }

    public function testMigrations(): void
    {
        $revisions = $this->provideDataForMigrations();

        if ([] === $revisions) {
            $this->markTestSkipped('No migrations to test');
        }

        foreach ($revisions as $originRevision) {
            foreach ($revisions as $targetRevision) {
                if ($originRevision['_revision'] < $targetRevision['_revision']) {
                    $this->assertMigrateTest($originRevision, $targetRevision);
                }
            }
        }
    }

    protected function readModuleConfiguration(): array
    {
        $moduleConfig = Yaml::parseFile("$this->modulePath/config.yaml");

        $processor = new Processor();

        return $processor->processConfiguration(new Module($this->moduleName), [$moduleConfig['module']]);
    }

    public function testConfiguration(): void
    {
        $config = $this->readModuleConfiguration();

        $this->assertIsArray($config);

        if (isset($config['edit_template'])) {
            $editTemplate = $config['edit_template'];
            $editTemplate = str_replace("@module/$this->moduleName/", '', $editTemplate);
            $this->assertFileExists("$this->modulePath/$editTemplate");
        }

        if (isset($config['form_template'])) {
            $formTemplate = $config['form_template'];
            $formTemplate = str_replace("@module/$this->moduleName/", '', $formTemplate);
            $this->assertFileExists("$this->modulePath/$formTemplate");
        }

        if (isset($config['render_template'])) {
            $renderTemplate = $config['render_template'];
            $renderTemplate = str_replace("@module/$this->moduleName/", '', $renderTemplate);
            $this->assertFileExists("$this->modulePath/$renderTemplate");
        }
    }

    protected function getModuleForm(array $config, $formData = null): FormInterface
    {
        if (DynamicFormModuleType::class != $config['module_type']) {
            $this->markTestSkipped(sprintf('ModuleTestCase does not support %s modules yet', $config['module_type']));
        }

        return $this->factory->create(DynamicFormModuleType::class, $formData, [
            'module_id' => $this->moduleName,
            'module_revision' => $config['revision'],
            'form_fields' => $config['module_options']['form_fields'],
            'content_type' => 'page',
            'content' => new Page(),
        ]);
    }

    public function testModuleForm(): void
    {
        $config = $this->readModuleConfiguration();
        $form = $this->getModuleForm($config);
        $view = $form->createView();
        $this->assertArrayHasKey('module_errors', $view->vars);
    }

    abstract public static function provideModuleRender(): array;

    /**
     * @dataProvider provideModuleRender
     * @throws Exception
     * @throws ModuleRenderException
     */
    #[DataProvider('provideModuleRender')]
    public function testRender(array $data, string|callable $expected, array $templatesSource = []): void
    {
        $request = new Request();
        $request->setLocale($this->defaultLocale);
        $request->setDefaultLocale($this->defaultLocale);

        $requestStack = new RequestStack();
        foreach ([$request] as $stackRequest) {
            $requestStack->push($stackRequest);
        }

        $moduleConfiguration = $this->readModuleConfiguration();
        $moduleConfiguration['revision_migration_scripts'] = [];
        $moduleConfiguration['revision'] = $data['_revision'] ?? 1;

        $cmsConfig = $this->createMock(CmsConfig::class);
        $cmsConfig->method('getModule')->willReturn($moduleConfiguration);

        if (!isset($templatesSource[$moduleConfiguration['render_template']])) {
            $templatesSource[$moduleConfiguration['render_template']] = file_get_contents(str_replace("@module/$this->moduleName", "$this->modulePath", $moduleConfiguration['render_template']));
        }

        $templatesSource['@SfsCms/macros/modules_render.html.twig'] = file_get_contents(__DIR__.'/../../templates/macros/modules_render.html.twig');

        $templateLoader = new ArrayLoader($templatesSource);
        $twig = new Environment($templateLoader, [
            'strict_variables' => true,
        ]);
        $twig->addGlobal('app', ['request' => $request]);
        $twig->addFilter(new TwigFilter('sfs_cms_trans', $this->translate(...), ['is_safe' => ['html']]));
        $twig->addFilter(new TwigFilter('sfs_media_render', $this->renderMedia(...), ['is_safe' => ['html']]));
        $twig->addFilter(new TwigFilter('sfs_media_render_image', $this->renderMedia(...), ['is_safe' => ['html']]));
        $twig->addFilter(new TwigFilter('sfs_media_render_picture', $this->renderMedia(...), ['is_safe' => ['html']]));
        $twig->addFilter(new TwigFilter('sfs_media_render_video', $this->renderMedia(...), ['is_safe' => ['html']]));
        $twig->addFilter(new TwigFilter('sfs_media_render_video_set', $this->renderMedia(...), ['is_safe' => ['html']]));
        $twig->addFunction(new TwigFunction('sfs_cms_link_attr', $this->generateLinkAttributes(...), ['is_safe' => ['html']]));
        $twig->addFunction(new TwigFunction('sfs_media_render', $this->renderMedia(...), ['is_safe' => ['html']]));
        $twig->addFunction(new TwigFunction('sfs_media_render_image', $this->renderMedia(...), ['is_safe' => ['html']]));
        $twig->addFunction(new TwigFunction('sfs_media_render_picture', $this->renderMedia(...), ['is_safe' => ['html']]));
        $twig->addFunction(new TwigFunction('sfs_media_render_video', $this->renderMedia(...), ['is_safe' => ['html']]));
        $twig->addFunction(new TwigFunction('sfs_media_render_video_set', $this->renderMedia(...), ['is_safe' => ['html']]));

        $moduleRenderer = new ModuleRenderer($cmsConfig, $requestStack, $twig, null);

        $renderError = new RenderErrorList();

        $debugCollectorData = [];
        $data['_module'] = $this->moduleName;
        $render = $moduleRenderer->render($data, $debugCollectorData, [], $renderError);

        if ($renderError->getErrors()) {
            $this->fail($renderError->getErrors()[0]['exception']->getMessage());
        }

        if (is_callable($expected)) {
            $expected($render);
        } else {
            $this->assertEquals($expected, $render);
        }
    }

    protected function translate(mixed $translatableText): string
    {
        if ($translatableText instanceof Translation) {
            return $translatableText->translate($this->defaultLocale);
        }

        if (!is_array($translatableText)) {
            return '';
        }

        if (isset($translatableText['_default'])) {
            return Translation::createFromArray($translatableText)->translate($this->defaultLocale);
        }

        return $translatableText[$this->defaultLocale] ?? '';
    }

    protected function renderMedia(mixed $media, ?string $version = null, array $attributes = []): string
    {
        if (empty($media)) {
            return '';
        }

        $mediaId = is_scalar($media) ? (string) $media : 'media';

        $renderedAttributes = [
            'data-media' => $mediaId,
        ];

        if ($version) {
            $renderedAttributes['data-version'] = $version;
        }

        if (isset($attributes['class']) && is_string($attributes['class'])) {
            $renderedAttributes['class'] = $attributes['class'];
        }

        return sprintf('<span %s></span>', implode(' ', array_map(
            static fn (string $name, string $value): string => sprintf('%s="%s"', $name, htmlentities($value)),
            array_keys($renderedAttributes),
            $renderedAttributes,
        )));
    }

    protected function generateLinkAttributes(array $linkData): string
    {
        $attributes = [];

        switch ($linkData['type'] ?? null) {
            case 'anchor':
                if (empty($linkData['anchor'])) {
                    return '';
                }

                $attributes['href'] = '#'.ltrim($linkData['anchor'], '#');
                break;

            case 'route':
                if (empty($linkData['route_name'])) {
                    return '';
                }

                $attributes['href'] = '/'.$linkData['route_name'];
                break;

            case 'url':
                if (empty($linkData['url'])) {
                    return '';
                }

                $attributes['href'] = $linkData['url'];
                break;

            default:
                return '';
        }

        if ('_self' !== ($linkData['target'] ?? '_self')) {
            $attributes['target'] = 'custom' !== $linkData['target'] ? $linkData['target'] : ($linkData['custom_target'] ?? '');
        }

        return implode(' ', array_map(
            static fn (string $name, string $value): string => sprintf('%s="%s"', $name, htmlentities($value)),
            array_keys($attributes),
            $attributes,
        ));
    }

    public static function assertRenderCrawler(callable $expected, string $render): void
    {
        $crawler = new Crawler($render);
        $expected($crawler);
    }

    public static function assertRenderText(string $expected, string $render, ?string $cssSelector = null, ?string $xpathSelector = null): void
    {
        ModuleTestCase::assertRenderCrawler(function (Crawler $crawler) use ($expected, $cssSelector, $xpathSelector): void {
            if ($cssSelector) {
                $crawler = $crawler->filter($cssSelector);
            } elseif ($xpathSelector) {
                $crawler = $crawler->filterXPath($xpathSelector);
            }

            self::assertTrue((bool) $crawler->count(), 'No node found for selector in node render');

            self::assertEquals($expected, $crawler->text());
        }, $render);
    }
}
