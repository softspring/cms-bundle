<?php

namespace Softspring\CmsBundle\Render;

use Psr\Log\LoggerInterface;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Form\Module\ContainerModuleType;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\Utils\ModuleMigrator;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Profiler\Profiler;
use Twig\Environment;

class ContentRender
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
        $this->twig = $twig;
        $this->cmsConfig = $cmsConfig;
        $this->requestStack = $requestStack;
        $this->cmsLogger = $cmsLogger;
        $this->profilerEnabled = (bool) $profiler;
    }

    public function render(ContentVersionInterface $version): string
    {
        $this->cmsLogger && $this->cmsLogger->debug(sprintf('Rendering %s page version', $version->getContent()->getName()));

        // preload all medias
        $version->getMedias();
        // preload all routes
        $version->getRoutes();

        $layout = $this->cmsConfig->getLayout($version->getLayout());

        $request = $this->requestStack->getCurrentRequest();

        $containers = $version->getCompiledModules()["{$request->attributes->get('_sfs_cms_site')}"][$request->getLocale()] ?? $this->renderModules($version);

        return $this->twig->render($layout['render_template'], [
            'containers' => $containers,
            'version' => $version,
            'content' => $version->getContent(),
        ]);
    }

    public function renderModules(ContentVersionInterface $version): array
    {
        // preload all medias
        $version->getMedias();
        // preload all routes
        $version->getRoutes();

        $layout = $this->cmsConfig->getLayout($version->getLayout());
        $versionData = $version->getData();

        $containers = [];
        foreach ($layout['containers'] as $layoutContainerId => $layoutContainerConfig) {
            $layoutContainer = $versionData ? $versionData[$layoutContainerId] ?? [] : [];
            $containers[$layoutContainerId] = '';

            foreach ($layoutContainer as $module) {
                $this->profilerDebugCollectorData[$layoutContainerId] = [];
                $containers[$layoutContainerId] .= $this->renderModule($module, $version, $this->profilerDebugCollectorData[$layoutContainerId]);
            }
        }

        return $containers;
    }

    protected function renderModule(array $module, ContentVersionInterface $version, array &$profilerDebugCollectorData): string
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

            foreach ($module['modules'] as $submodule) {
                $module['contents'][] = $this->renderModule($submodule, $version, $profilerDebugCollectorData[sizeof($profilerDebugCollectorData) - 1]['modules']);
            }

            return $this->twig->render($moduleConfig['render_template'], $module);
        }

        $module += [
            '_config' => $moduleConfig,
            '_version' => $version,
            '_content' => $version->getContent(),
        ];

        $profilerDebugCollectorData[] = [
            'config' => $moduleConfig,
        ];

        return $this->twig->render($moduleConfig['render_template'], $module);
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
