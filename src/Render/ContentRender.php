<?php

namespace Softspring\CmsBundle\Render;

use Psr\Log\LoggerInterface;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Form\Module\ContainerModuleType;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\Model\SiteInterface;
use Softspring\CmsBundle\Utils\ModuleMigrator;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Profiler\Profiler;
use Twig\Environment;

class ContentRender extends AbstractRenderer
{
    public const SITE_HIDDEN_MODULE = '<!-- site hidden module -->';
    public const LOCALE_HIDDEN_MODULE = '<!-- locale hidden module -->';

    protected Environment $twig;

    protected CmsConfig $cmsConfig;

    protected RequestStack $requestStack;

    protected ?LoggerInterface $cmsLogger;

    protected bool $profilerEnabled;

    protected array $profilerDebugCollectorData = [];

    public function __construct(Environment $twig, CmsConfig $cmsConfig, RequestStack $requestStack, ?LoggerInterface $cmsLogger, ?Profiler $profiler)
    {
        parent::__construct($requestStack);
        $this->twig = $twig;
        $this->cmsConfig = $cmsConfig;
        $this->cmsLogger = $cmsLogger;
        $this->profilerEnabled = (bool) $profiler;
    }

    public function render(ContentVersionInterface $version, RenderErrorList $renderErrorList = null): string
    {
        $this->cmsLogger && $this->cmsLogger->debug(sprintf('Rendering %s page version', $version->getContent()->getName()));

        // preload all medias
        $version->getMedias();
        // preload all routes
        $version->getRoutes();

        $layout = $this->cmsConfig->getLayout($version->getLayout());

        $request = $this->requestStack->getCurrentRequest();

        $site = $request->attributes->get('_sfs_cms_site');
        $locale = $request->getLocale();

        $containers = $version->getCompiledModules()["$site"][$locale] ?? $this->renderModules($version, $site, $locale, $renderErrorList);

        return $this->twig->render($layout['render_template'], [
            'containers' => $containers,
            'version' => $version,
            'content' => $version->getContent(),
        ]);
    }

    public function renderModules(ContentVersionInterface $version, SiteInterface $site, string $locale, RenderErrorList $renderErrorList = null): array
    {
        return $this->encapsulateRequestRender($site, $locale, function () use ($version, $renderErrorList): array {
            // preload all medias
            $version->getMedias();
            // preload all routes
            $version->getRoutes();

            $layout = $this->cmsConfig->getLayout($version->getLayout());
            $versionData = $version->getData();

            $containers = [];
            $renderErrorList && $renderErrorList->resetLocation();
            $renderErrorList && $renderErrorList->pushLocation('data');
            foreach ($layout['containers'] as $layoutContainerId => $layoutContainerConfig) {
                $layoutContainer = $versionData ? $versionData[$layoutContainerId] ?? [] : [];
                $containers[$layoutContainerId] = '';

                $renderErrorList && $renderErrorList->pushLocation($layoutContainerId);
                foreach ($layoutContainer as $i => $module) {
                    $this->profilerDebugCollectorData[$layoutContainerId] = [];
                    $renderErrorList && $renderErrorList->pushLocation($i);
                    $containers[$layoutContainerId] .= $this->renderModule($module, $version, $this->profilerDebugCollectorData[$layoutContainerId], $renderErrorList);
                    $renderErrorList && $renderErrorList->popLocation();
                }
                $renderErrorList && $renderErrorList->popLocation();
            }

            return $containers;
        });
    }

    protected function renderModule(array $module, ContentVersionInterface $version, array &$profilerDebugCollectorData, RenderErrorList $renderErrorList = null): string
    {
        if (isset($module['site_filter'])) {
            $currentSite = $this->requestStack->getCurrentRequest()->get('_sfs_cms_site');

            $siteFilters = [];
            foreach ($module['site_filter'] as $site) {
                $siteFilters[] = is_string($site) ? $this->cmsConfig->getSite($site) : $site;
            }

            if (!in_array($currentSite, $siteFilters)) {
                return self::SITE_HIDDEN_MODULE;
            }
        }

        if (isset($module['locale_filter'])) {
            $currentLocale = $this->requestStack->getCurrentRequest()->getLocale();

            if (!in_array($currentLocale, $module['locale_filter'])) {
                return self::LOCALE_HIDDEN_MODULE;
            }
        }

        $this->cmsLogger && $this->cmsLogger->debug(sprintf('Rendering %s module', $module['_module']));

        $moduleConfig = $this->cmsConfig->getModule($module['_module']);

        $module = ModuleMigrator::migrate($moduleConfig['revision_migration_scripts'], $module, $moduleConfig['revision']);

        if ($this->isContainer($module)) {
            $module['contents'] = [];

            $profilerDebugCollectorData[] = [
                'config' => $moduleConfig,
                'modules' => [],
            ];

            $renderErrorList && $renderErrorList->pushLocation('modules');
            foreach ($module['modules'] as $i => $submodule) {
                $renderErrorList && $renderErrorList->pushLocation($i);
                $module['contents'][] = $this->renderModule($submodule, $version, $profilerDebugCollectorData[sizeof($profilerDebugCollectorData) - 1]['modules'], $renderErrorList);
                $renderErrorList && $renderErrorList->popLocation();
            }
            $renderErrorList && $renderErrorList->popLocation();

            try {
                return $this->twig->render($moduleConfig['render_template'], $module);
            } catch (\Exception $exception) {
                $this->cmsLogger && $this->cmsLogger->error(sprintf('Error rendering %s template: %s', $moduleConfig['render_template'], $exception->getMessage()));
                $renderErrorList && $renderErrorList->add($moduleConfig['render_template'], $exception, [
                    'moduleConfig' => $moduleConfig,
                    'moduleData' => $module,
                ]);

                return '<div class="alert alert-danger" role="alert"><!-- MODULE_RENDER_ERROR -->We\'re sorry, an error has been produced rendering this content, please review content configuration and try again. If the problem persist talk to developers.</div>';
            }
        }

        $module += [
            '_config' => $moduleConfig,
            '_version' => $version,
            '_content' => $version->getContent(),
        ];

        $profilerDebugCollectorData[] = [
            'config' => $moduleConfig,
        ];

        try {
            return $this->twig->render($moduleConfig['render_template'], $module);
        } catch (\Exception $exception) {
            $this->cmsLogger && $this->cmsLogger->error(sprintf('Error rendering %s template: %s %s', $moduleConfig['render_template'], $exception->getMessage(), $renderErrorList ? $renderErrorList->currentLocation() : ''));
            $renderErrorList && $renderErrorList->add($moduleConfig['render_template'], $exception, [
                'moduleConfig' => $moduleConfig,
                'moduleData' => $module,
            ]);

            return '<div class="alert alert-danger" role="alert"><!-- MODULE_RENDER_ERROR -->We\'re sorry, an error has been produced rendering this content, please review content configuration and try again. If the problem persist talk to developers.</div>';
        }
    }

    private function isContainer($module): bool
    {
        $module = $this->cmsConfig->getModule($module['_module']);

        return ContainerModuleType::class === $module['module_type'];
    }

    public function getDebugCollectorData(): array
    {
        return $this->profilerDebugCollectorData;
    }
}
