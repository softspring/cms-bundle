<?php

namespace Softspring\CmsBundle\Render;

use Exception;
use Psr\Log\LoggerInterface;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Config\Exception\DisabledModuleException;
use Softspring\CmsBundle\Config\Exception\InvalidModuleException;
use Softspring\CmsBundle\Config\Exception\InvalidSiteException;
use Softspring\CmsBundle\Form\Module\ContainerModuleType;
use Softspring\CmsBundle\Render\Error\RenderErrorList;
use Softspring\CmsBundle\Render\Exception\ModuleRenderException;
use Softspring\CmsBundle\Utils\DataMigrator;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;

class ModuleRenderer
{
    public const SITE_HIDDEN_MODULE = '<!-- SITE_HIDDEN_MODULE -->';
    public const LOCALE_HIDDEN_MODULE = '<!-- LOCALE_HIDDEN_MODULE -->';
    public const DISABLED_HIDDEN_MODULE = '<!-- DISABLED_HIDDEN_MODULE -->';

    public function __construct(
        protected CmsConfig $cmsConfig,
        protected RequestStack $requestStack,
        protected Environment $twig,
        protected ?LoggerInterface $cmsLogger,
    ) {
    }

    /**
     * @throws ModuleRenderException
     */
    public function render(array $moduleData, array &$profilerDebugCollectorData, array $twigAdditionalContext = [], ?RenderErrorList $renderErrorList = null): string
    {
        try {
            if ($this->skipModuleRenderBySiteFilter($moduleData)) {
                return self::SITE_HIDDEN_MODULE."\n";
            }
        } catch (InvalidSiteException $e) {
            throw new ModuleRenderException($moduleData, $e);
        }

        if ($this->skipModuleRenderByLocaleFilter($moduleData)) {
            return self::LOCALE_HIDDEN_MODULE."\n";
        }

        $this->cmsLogger && $this->cmsLogger->debug(sprintf('Rendering %s module', $moduleData['_module']));

        try {
            $moduleConfig = $this->cmsConfig->getModule($moduleData['_module']);
        } catch (InvalidModuleException $e) {
            throw new ModuleRenderException($moduleData, $e);
        } catch (DisabledModuleException) {
            $this->cmsLogger && $this->cmsLogger->warning(sprintf('Module %s is disabled, but it is rendered.', $moduleData['_module']));

            return self::DISABLED_HIDDEN_MODULE."\n";
        }

        $moduleData = DataMigrator::migrate($moduleConfig['revision_migration_scripts'], $moduleData, $moduleConfig['revision']);

        if ($this->isContainer($moduleConfig)) {
            return $this->renderContainerModule($moduleData, $moduleConfig, $profilerDebugCollectorData, $twigAdditionalContext, $renderErrorList);
        } else {
            return $this->renderNoContainerModule($moduleData, $moduleConfig, $profilerDebugCollectorData, $twigAdditionalContext, $renderErrorList);
        }
    }

    /**
     * @throws InvalidSiteException
     */
    protected function skipModuleRenderBySiteFilter(array $module): bool
    {
        if (isset($module['site_filter'])) {
            $currentSite = $this->requestStack->getCurrentRequest()->get('_sfs_cms_site');

            $siteFilters = [];
            foreach ($module['site_filter'] as $site) {
                $siteFilters[] = is_string($site) ? $this->cmsConfig->getSite($site) : $site;
            }

            if (!in_array($currentSite, $siteFilters)) {
                return true;
            }
        }

        return false;
    }

    protected function skipModuleRenderByLocaleFilter(array $module): bool
    {
        if (isset($module['locale_filter'])) {
            $currentLocale = $this->requestStack->getCurrentRequest()->getLocale();

            if (!in_array($currentLocale, $module['locale_filter'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @throws ModuleRenderException
     */
    protected function renderContainerModule(array $module, array $moduleConfig, array &$profilerDebugCollectorData, array $twigAdditionalContext = [], ?RenderErrorList $renderErrorList = null): string
    {
        $module['contents'] = [];

        $profilerDebugCollectorData[] = [
            'config' => $moduleConfig,
            'modules' => [],
        ];

        $renderErrorList && $renderErrorList->pushLocation('modules');
        foreach ($module['modules'] as $i => $submodule) {
            $renderErrorList && $renderErrorList->pushLocation($i);
            $module['contents'][] = $this->render($submodule, $profilerDebugCollectorData[sizeof($profilerDebugCollectorData) - 1]['modules'], $twigAdditionalContext, $renderErrorList);
            $renderErrorList && $renderErrorList->popLocation();
        }
        $renderErrorList && $renderErrorList->popLocation();

        try {
            // return $this->isolatedRunner->isolateRequestRender()

            return $this->twig->render($moduleConfig['render_template'], $module);
        } catch (Exception $exception) {
            $this->cmsLogger && $this->cmsLogger->error(sprintf('Error rendering %s template: %s', $moduleConfig['render_template'], $exception->getMessage()));

            if (!$renderErrorList) {
                throw new ModuleRenderException($module, $exception);
            }

            $renderErrorList->add($moduleConfig['render_template'], $exception, [
                'moduleConfig' => $moduleConfig,
                'moduleData' => $module,
            ]);

            return '<div class="alert alert-danger" role="alert"><!-- MODULE_RENDER_ERROR -->We\'re sorry, an error has been produced rendering this content, please review content configuration and try again. If the problem persist talk to developers.</div>';
        }
    }

    /**
     * @throws ModuleRenderException
     */
    protected function renderNoContainerModule(array $moduleData, array $moduleConfig, array &$profilerDebugCollectorData, array $twigAdditionalContext = [], ?RenderErrorList $renderErrorList = null): string
    {
        $twigContext = array_merge($moduleData, $twigAdditionalContext, [
            '_config' => $moduleConfig,
        ]);

        $profilerDebugCollectorData[] = [
            'config' => $moduleConfig,
        ];

        try {
            return $this->twig->render($moduleConfig['render_template'], $twigContext);
        } catch (Exception $exception) {
            $this->cmsLogger && $this->cmsLogger->error(sprintf('Error rendering %s template: %s %s', $moduleConfig['render_template'], $exception->getMessage(), $renderErrorList ? $renderErrorList->currentLocation() : ''));

            if (!$renderErrorList) {
                throw new ModuleRenderException($moduleData, $exception);
            }

            $renderErrorList->add($moduleConfig['render_template'], $exception, [
                'moduleConfig' => $moduleConfig,
                'moduleData' => $moduleData,
                'twigAdditionalContext' => $twigAdditionalContext,
            ]);

            return '<div class="alert alert-danger" role="alert"><!-- MODULE_RENDER_ERROR -->We\'re sorry, an error has been produced rendering this content, please review content configuration and try again. If the problem persist talk to developers.</div>';
        }
    }

    protected function isContainer(array $moduleConfig): bool
    {
        return ContainerModuleType::class === $moduleConfig['module_type'];
    }
}
