<?php

namespace Softspring\CmsBundle\Render;

use Softspring\CmsBundle\Config\CmsConfig;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpCache\Esi;
use Symfony\Component\HttpKernel\Profiler\Profiler;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;
use Twig\Environment;

class MenuRenderer extends AbstractRenderer
{
    protected bool $profilerEnabled;
    protected bool $esiEnabled;
    protected array $profilerDebugCollectorData = [];
    protected ?EntrypointLookupInterface $entrypointLookup;

    public function __construct(
        RequestStack $requestStack,
        protected CmsConfig $cmsConfig,
        protected Environment $twig,
        ?EntrypointLookupInterface $entrypointLookup,
        ?Profiler $profiler,
        ?Esi $esi
    ) {
        parent::__construct($requestStack, $entrypointLookup);
        $this->profilerEnabled = (bool) $profiler;
        $this->esiEnabled = (bool) $esi;
    }

    public function renderMenuByType(string $type): string
    {
        $menuConfig = $this->cmsConfig->getMenu($type);

        if ($menuConfig['esi'] && !$this->isPreview()) {
            if (!$this->esiEnabled) {
                throw new \Exception('You must enable esi with framework.esi configuration to use it in CMS');
            }

            $renderFunction = 'render_esi';
        } else {
            $renderFunction = 'render';
        }

        $previewJsonProperty = $this->isPreview() ? ",'_cms_preview':true" : '';

        $twigCode = "{{ $renderFunction(controller('Softspring\\\\CmsBundle\\\\Controller\\\\MenuController::renderByType', {'type':'$type'$previewJsonProperty})) }}";

        $template = twig_template_from_string($this->twig, $twigCode);

        if ($this->profilerEnabled) {
            $this->profilerDebugCollectorData[] = [
                'type' => $type,
                'config' => $menuConfig,
            ];
        }

        return $this->encapsulateEsiCapableRender(function () use ($template) { return $template->render(); });
    }

    public function getDebugCollectorData(): array
    {
        return $this->profilerDebugCollectorData;
    }
}
