<?php

namespace Softspring\CmsBundle\Render;

use Exception;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Config\Exception\InvalidMenuException;
use Softspring\CmsBundle\Render\Exception\RenderException;
use Softspring\CmsBundle\Render\Isolated\IsolatedRunner;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpCache\Esi;
use Symfony\Component\HttpKernel\Profiler\Profiler;
use Twig\Environment;

class MenuRenderer
{
    protected bool $profilerEnabled;
    protected bool $esiEnabled;
    protected array $profilerDebugCollectorData = [];

    public function __construct(
        protected RequestStack $requestStack,
        protected CmsConfig $cmsConfig,
        protected Environment $twig,
        protected IsolatedRunner $isolatedRunner,
        protected ?Profiler $profiler,
        protected ?Esi $esi,
    ) {
        $this->profilerEnabled = (bool) $profiler;
        $this->esiEnabled = (bool) $esi;
    }

    /**
     * @throws RenderException
     * @throws InvalidMenuException
     * @throws Exception
     */
    public function renderMenuByType(string $type, ?string $locale = null): string
    {
        $locale = $locale ?? $this->requestStack->getCurrentRequest()?->getLocale();
        $site = $this->requestStack->getCurrentRequest()?->attributes->get('_sfs_cms_site');
        $menuConfig = $this->cmsConfig->getMenu($type);

        if ($menuConfig['esi'] && !$this->isPreview()) {
            if (!$this->esiEnabled) {
                throw new Exception('You must enable esi with framework.esi configuration to use it in CMS');
            }

            $renderFunction = 'render_esi';
        } else {
            $renderFunction = 'render';
        }

        $previewJsonProperty = $this->isPreview() ? ",'_cms_preview':true" : '';
        $previewJsonProperty .= $locale ? ",'_locale':'$locale'" : '';

        $twigCode = "{{ $renderFunction(controller('Softspring\\\\CmsBundle\\\\Controller\\\\MenuController::renderByType', {'type':'$type'$previewJsonProperty})) }}";

        $template = twig_template_from_string($this->twig, $twigCode);

        if ($this->profilerEnabled) {
            $this->profilerDebugCollectorData[] = [
                'type' => $type,
                'config' => $menuConfig,
            ];
        }

        return $this->isolatedRunner->isolateEsiCapableRequestRender(function () use ($template) { return $template->render(); });
    }

    public function getDebugCollectorData(): array
    {
        return $this->profilerDebugCollectorData;
    }

    protected function isPreview(): bool
    {
        return $this->requestStack->getCurrentRequest()?->attributes->has('_cms_preview') ?: false;
    }
}
