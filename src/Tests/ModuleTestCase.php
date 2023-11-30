<?php

namespace Softspring\CmsBundle\Tests;

use Doctrine\ORM\EntityRepository;
use Softspring\CmsBundle\Config\Model\Module;
use Softspring\CmsBundle\Entity\Page;
use Softspring\CmsBundle\Form\Extension\DefaultValueExtension;
use Softspring\CmsBundle\Form\Extension\DynamicTypesExtension;
use Softspring\CmsBundle\Form\Module\DynamicFormModuleType;
use Softspring\CmsBundle\Form\Resolver\TypeResolver;
use Softspring\CmsBundle\Form\Type\LinkType;
use Softspring\CmsBundle\Form\Type\SymfonyRouteType;
use Softspring\CmsBundle\Form\Type\TranslatableTextType;
use Softspring\CmsBundle\Form\Type\TranslatableType;
use Softspring\CmsBundle\Helper\CmsHelper;
use Softspring\CmsBundle\Manager\RouteManagerInterface;
use Softspring\CmsBundle\Utils\ModuleMigrator;
use Softspring\Component\DynamicFormType\Form\Extension\DynamicFormExtension;
use Softspring\Component\DynamicFormType\Form\Resolver\ConstraintResolver;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\Traits\ValidatorExtensionTrait;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Yaml\Yaml;

abstract class ModuleTestCase extends TypeTestCase
{
    use ValidatorExtensionTrait;

    protected string $moduleName = '';
    protected string $modulePath = '';
    protected string $defaultLocale = 'en';
    protected array $enabledLocales = ['es', 'en'];

    protected function getExtensions(): array
    {
        $cmsTypeResolver = new TypeResolver();

        $router = $this->createMock(RouterInterface::class);
        $router->method('getRouteCollection')->willReturn(new RouteCollection());

        $routeRepository = $this->createMock(EntityRepository::class);
        $routeRepository->method('findAll')->willReturn([]);

        $routeManager = $this->createMock(RouteManagerInterface::class);
        $routeManager->method('getRepository')->willReturn($routeRepository);

        $cmsHelper = $this->createMock(CmsHelper::class);

        $preloadedFormTypes = [];
        $preloadedFormTypes[] = new DynamicFormModuleType($cmsHelper);
        $preloadedFormTypes[] = new TranslatableTextType($this->defaultLocale, $this->enabledLocales);
        $preloadedFormTypes[] = new TranslatableType($this->defaultLocale, $this->enabledLocales);
        $preloadedFormTypes[] = new SymfonyRouteType($router, $routeManager, []);
        $preloadedFormTypes[] = new LinkType($router, $routeManager, []);

        return [
            $this->getValidatorExtension(),
            new DynamicFormExtension($cmsTypeResolver, new ConstraintResolver()),
            new PreloadedExtension($preloadedFormTypes, [DynamicFormModuleType::class => [new DynamicTypesExtension($cmsTypeResolver)]]),
            new DefaultValueExtension(),
        ];
    }

    protected function assertMigrateTest(array $originData, array $expectedData): void
    {
        $migratedData = ModuleMigrator::migrate(["{$this->modulePath}/migrate.php"], $originData, $expectedData['_revision']);
        $this->assertEquals($expectedData, $migratedData);
    }

    abstract protected function provideDataForMigrations(): array;

    public function testMigrations(): void
    {
        $revisions = $this->provideDataForMigrations();

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
        $moduleConfig = Yaml::parseFile("{$this->modulePath}/config.yaml");

        $processor = new Processor();

        return $processor->processConfiguration(new Module($this->moduleName), [$moduleConfig['module']]);
    }

    public function testConfiguration(): void
    {
        $config = $this->readModuleConfiguration();

        $this->assertIsArray($config);

        if (isset($config['edit_template'])) {
            $editTemplate = $config['edit_template'];
            $editTemplate = str_replace("@module/{$this->moduleName}/", '', $editTemplate);
            $this->assertFileExists("{$this->modulePath}/$editTemplate");
        }

        if (isset($config['form_template'])) {
            $formTemplate = $config['form_template'];
            $formTemplate = str_replace("@module/{$this->moduleName}/", '', $formTemplate);
            $this->assertFileExists("{$this->modulePath}/$formTemplate");
        }

        if (isset($config['render_template'])) {
            $renderTemplate = $config['render_template'];
            $renderTemplate = str_replace("@module/{$this->moduleName}/", '', $renderTemplate);
            $this->assertFileExists("{$this->modulePath}/$renderTemplate");
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
}
