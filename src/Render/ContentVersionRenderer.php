<?php

namespace Softspring\CmsBundle\Render;

use Psr\Log\LoggerInterface;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Config\Exception\DisabledModuleException;
use Softspring\CmsBundle\Config\Exception\InvalidLayoutException;
use Softspring\CmsBundle\Config\Exception\InvalidModuleException;
use Softspring\CmsBundle\Config\Exception\InvalidSiteException;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\Model\SiteInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Profiler\Profiler;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class ContentVersionRenderer extends AbstractRenderer
{
    protected bool $profilerEnabled;

    protected array $profilerDebugCollectorData = [];

    public function __construct(
        protected Environment $twig,
        protected CmsConfig $cmsConfig,
        protected RequestStack $requestStack,
        protected ModuleRenderer $moduleRenderer,
        protected ?LoggerInterface $cmsLogger,
        ?Profiler $profiler
    ) {
        parent::__construct($requestStack);
        $this->profilerEnabled = (bool) $profiler;
    }

    /**
     * @throws DisabledModuleException
     * @throws InvalidLayoutException
     * @throws InvalidModuleException
     * @throws InvalidSiteException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
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

    /**
     * @throws DisabledModuleException
     * @throws InvalidModuleException
     * @throws InvalidSiteException
     * @throws InvalidLayoutException
     */
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
                    $containers[$layoutContainerId] .= $this->moduleRenderer->render($module, $version, $this->profilerDebugCollectorData[$layoutContainerId], $renderErrorList);
                    $renderErrorList && $renderErrorList->popLocation();
                }
                $renderErrorList && $renderErrorList->popLocation();
            }

            return $containers;
        });
    }

    /**
     * @deprecated this is not used anymore, will be removed in next major version
     *
     * @throws InvalidSiteException
     * @throws DisabledModuleException
     * @throws InvalidModuleException
     */
    public function renderModuleById(string $moduleId, array $data, RenderErrorList $renderErrorList = null): string
    {
        $moduleConfig = $this->cmsConfig->getModule($moduleId);

        $module = $data;
        $module['_module'] = $moduleId;
        // simulate it is latest module revision
        $module['_revision'] = $moduleConfig['revision'];

        $profilerDebugCollectorData = [];

        return $this->moduleRenderer->render($module, null, $profilerDebugCollectorData, $renderErrorList);
    }

    public function getDebugCollectorData(): array
    {
        return $this->profilerDebugCollectorData;
    }
}