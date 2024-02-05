<?php

namespace Softspring\CmsBundle\Render;

use Psr\Log\LoggerInterface;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Config\Exception\DisabledModuleException;
use Softspring\CmsBundle\Config\Exception\InvalidModuleException;
use Softspring\CmsBundle\Config\Exception\InvalidSiteException;
use Softspring\CmsBundle\Form\Module\ContainerModuleType;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\Utils\DataMigrator;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;

class ModuleRenderer
{
    public const SITE_HIDDEN_MODULE = '<!-- site hidden module -->';
    public const LOCALE_HIDDEN_MODULE = '<!-- locale hidden module -->';

    public function __construct(
        protected CmsConfig $cmsConfig,
        protected RequestStack $requestStack,
        protected ?LoggerInterface $cmsLogger,
        protected Environment $twig
    ) {
    }

    /**
     * @throws InvalidSiteException
     * @throws DisabledModuleException
     * @throws InvalidModuleException
     */
    public function render(array $module, ?ContentVersionInterface $version, array &$profilerDebugCollectorData, ?RenderErrorList $renderErrorList = null): string
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

        $module = DataMigrator::migrate($moduleConfig['revision_migration_scripts'], $module, $moduleConfig['revision']);

        if ($this->isContainer($module)) {
            $module['contents'] = [];

            $profilerDebugCollectorData[] = [
                'config' => $moduleConfig,
                'modules' => [],
            ];

            $renderErrorList && $renderErrorList->pushLocation('modules');
            foreach ($module['modules'] as $i => $submodule) {
                $renderErrorList && $renderErrorList->pushLocation($i);
                $module['contents'][] = $this->render($submodule, $version, $profilerDebugCollectorData[sizeof($profilerDebugCollectorData) - 1]['modules'], $renderErrorList);
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
            '_content' => $version?->getContent(),
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

    /**
     * @throws DisabledModuleException
     * @throws InvalidModuleException
     */
    private function isContainer($module): bool
    {
        $module = $this->cmsConfig->getModule($module['_module']);

        return ContainerModuleType::class === $module['module_type'];
    }
}
