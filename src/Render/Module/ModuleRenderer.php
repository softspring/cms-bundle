<?php

namespace Softspring\CmsBundle\Render\Module;

use Exception;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Config\Exception\DisabledModuleException;
use Softspring\CmsBundle\Config\Exception\InvalidModuleException;
use Softspring\CmsBundle\Config\Exception\InvalidSiteException;
use Softspring\CmsBundle\Form\Module\ContainerModuleType;
use Softspring\CmsBundle\Model\SiteInterface;
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
                $this->cmsLogger && $this->cmsLogger->debug(sprintf('Skipping %s module render by site', $moduleData['_module']));

                return self::SITE_HIDDEN_MODULE."\n";
            }
        } catch (InvalidSiteException $e) {
            throw new ModuleRenderException($moduleData, $e);
        }

        if ($this->skipModuleRenderByLocaleFilter($moduleData)) {
            $this->cmsLogger && $this->cmsLogger->debug(sprintf('Skipping %s module render by locale', $moduleData['_module']));

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
        }

        return $this->renderNoContainerModule($moduleData, $moduleConfig, $profilerDebugCollectorData, $twigAdditionalContext, $renderErrorList);
    }

    /**
     * @throws InvalidSiteException
     */
    protected function skipModuleRenderBySiteFilter(array $module): bool
    {
        if (!isset($module['site_filter'])) {
            return false;
        }

        $currentSite = $this->requestStack->getCurrentRequest()->get('_sfs_cms_site');

        $moduleEnabledSites = [];
        foreach ($module['site_filter'] as $key => $value) {
            if (is_string($key) && true === $value) {
                $moduleEnabledSites[] = $this->cmsConfig->getSite($key);
            } elseif (is_string($value)) {
                /** @deprecated, in 6.0 old format will be removed */
                $moduleEnabledSites[] = $this->cmsConfig->getSite($value);
            } elseif ($value instanceof SiteInterface) {
                /** @deprecated, in 6.0 old format will be removed */
                $moduleEnabledSites[] = $value;
            }
        }

        return !in_array($currentSite, $moduleEnabledSites);
    }

    protected function skipModuleRenderByLocaleFilter(array $module): bool
    {
        if (!isset($module['locale_filter'])) {
            return false;
        }

        $currentLocale = $this->requestStack->getCurrentRequest()->getLocale();

        $moduleEnabledLocales = array_keys(array_filter($module['locale_filter'], function ($value) {
            return true === $value;
        }));

        return !in_array($currentLocale, $moduleEnabledLocales);
    }

    /**
     * @throws ModuleRenderException
     * @throws RuntimeException
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
            $module['contents'][] = $this->render($submodule, $profilerDebugCollectorData[count($profilerDebugCollectorData) - 1]['modules'], $twigAdditionalContext, $renderErrorList);
            $renderErrorList && $renderErrorList->popLocation();
        }
        $renderErrorList && $renderErrorList->popLocation();

        try {
            // return $this->isolatedRunner->isolateRequestRender()

            return $this->twig->render($moduleConfig['render_template'], $module);
        } catch (Exception $exception) {
            $this->cmsLogger && $this->cmsLogger->error(sprintf('Error rendering %s template: %s', $moduleConfig['render_template'], $exception->getMessage()));

            if (!$renderErrorList instanceof RenderErrorList) {
                throw new ModuleRenderException($module, $exception);
            }

            $renderErrorList->add($moduleConfig['render_template'], $exception, [
                'moduleConfig' => $moduleConfig,
                'moduleData' => $module,
            ]);

            try {
                return $this->twig->render('@SfsCms/errors/module_render_error.html.twig', [
                    'isContainer' => true,
                    'moduleConfig' => $moduleConfig,
                    'moduleData' => $module,
                ]);
            } catch (Exception $e) {
                throw new RuntimeException('Fatal error rendering module error template', 0, $e);
            }
        }
    }

    /**
     * @throws ModuleRenderException
     * @throws RuntimeException
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
            $this->cmsLogger && $this->cmsLogger->error(sprintf('Error rendering %s template: %s %s', $moduleConfig['render_template'], $exception->getMessage(), $renderErrorList instanceof RenderErrorList ? $renderErrorList->currentLocation() : ''));

            if (!$renderErrorList instanceof RenderErrorList) {
                throw new ModuleRenderException($moduleData, $exception);
            }

            $renderErrorList->add($moduleConfig['render_template'], $exception, [
                'moduleConfig' => $moduleConfig,
                'moduleData' => $moduleData,
                'twigAdditionalContext' => $twigAdditionalContext,
            ]);

            try {
                return $this->twig->render('@SfsCms/errors/module_render_error.html.twig', [
                    'isContainer' => false,
                    'moduleConfig' => $moduleConfig,
                    'moduleData' => $moduleData,
                    'twigAdditionalContext' => $twigAdditionalContext,
                ]);
            } catch (Exception $e) {
                throw new RuntimeException('Fatal error rendering module error template', 0, $e);
            }
        }
    }

    protected function isContainer(array $moduleConfig): bool
    {
        return ContainerModuleType::class === $moduleConfig['module_type'];
    }
}
