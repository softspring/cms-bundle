<?php

namespace Softspring\CmsBundle\Render;

use Exception;
use Psr\Log\LoggerInterface;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Config\Exception\DisabledModuleException;
use Softspring\CmsBundle\Config\Exception\InvalidModuleException;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\Render\Error\RenderErrorList;
use Softspring\CmsBundle\Render\Exception\ModuleRenderException;
use Softspring\CmsBundle\Render\Exception\RenderException;
use Softspring\CmsBundle\Render\Isolated\IsolatedRunner;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Profiler\Profiler;
use Twig\Environment;

class ContentVersionRenderer implements ContentVersionRendererInterface
{
    protected bool $profilerEnabled;

    protected array $profilerDebugCollectorData = [];

    public function __construct(
        protected CmsConfig $cmsConfig,
        protected RequestStack $requestStack,
        protected IsolatedRunner $isolatedRunner,
        protected ?LoggerInterface $cmsLogger,
        ?Profiler $profiler,
    ) {
        $this->profilerEnabled = (bool) $profiler;
    }

    /**
     * @throws RenderException
     */
    public function render(ContentVersionInterface $version, Request $request, ?RenderErrorList $renderErrorList = null, ?array $compiledModules = null): string
    {
        return $this->isolatedRunner->isolateRequestRender($request, function (Request $request, Environment $twig, ModuleRenderer $moduleRenderer) use ($version, $renderErrorList, $compiledModules): string {
            try {
                $this->cmsLogger && $this->cmsLogger->debug(sprintf('Rendering %s content version', $version->getContent()->getName()));

                // preload all medias
                $version->getMedias();
                // preload all routes
                $version->getRoutes();

                $layout = $this->cmsConfig->getLayout($version->getLayout());

                $request = $this->requestStack->getCurrentRequest();
                $request->attributes->set('_route', $version->getContent()->getRoutes()->first()?->getId());

                $containers = $compiledModules ?? $this->renderModules($version, $request, $renderErrorList);

                return $twig->render($layout['render_template'], [
                    'containers' => $containers,
                    'version' => $version,
                    'content' => $version->getContent(),
                ]);
            } catch (Exception $e) {
                throw new RenderException(sprintf('Error rendering content version v%s', $version->getVersionNumber()), 0, $e);
            }
        });
    }

    /**
     * @throws RenderException
     */
    public function renderModules(ContentVersionInterface $version, Request $request, ?RenderErrorList $renderErrorList = null): array
    {
        return $this->isolatedRunner->isolateRequestRender($request, function (Request $request, Environment $twig, ModuleRenderer $moduleRenderer) use ($version, $renderErrorList): array {
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
                    $twigAdditionalContext = [
                        'version' => $version,
                        'content' => $version->getContent(),
                    ];
                    $containers[$layoutContainerId] .= $moduleRenderer->render($module, $this->profilerDebugCollectorData[$layoutContainerId], $twigAdditionalContext, $renderErrorList);
                    $renderErrorList && $renderErrorList->popLocation();
                }
                $renderErrorList && $renderErrorList->popLocation();
            }

            return $containers;
        });
    }

    /**
     * @throws DisabledModuleException
     * @throws InvalidModuleException
     * @throws ModuleRenderException
     *
     * @deprecated this is not used anymore, will be removed in next major version
     */
    public function renderModuleById(string $moduleId, array $data, ?RenderErrorList $renderErrorList = null): string
    {
        trigger_deprecation('softspring/cms-bundle', '5.1', 'The method "%s" is deprecated and will be removed in the next major version.', __METHOD__);

        return '';
    }

    public function getDebugCollectorData(): array
    {
        return $this->profilerDebugCollectorData;
    }
}
