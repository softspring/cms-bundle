<?php

namespace Softspring\CmsBundle\Render;

use Exception;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Config\Exception\InvalidMenuException;
use Softspring\CmsBundle\Render\Exception\RenderException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpCache\Esi;
use Symfony\Component\HttpKernel\Profiler\Profiler;
use Symfony\Component\Routing\RouterInterface;
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
        RouterInterface $router,
        ?EntrypointLookupInterface $entrypointLookup,
        ?Profiler $profiler,
        ?Esi $esi,
    ) {
        parent::__construct($requestStack, $entrypointLookup, $router);
        $this->profilerEnabled = (bool) $profiler;
        $this->esiEnabled = (bool) $esi;
    }

    /**
     * @throws RenderException
     * @throws InvalidMenuException
     */
    public function renderMenuByType(string $type): string
    {
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
