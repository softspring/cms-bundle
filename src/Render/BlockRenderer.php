<?php

namespace Softspring\CmsBundle\Render;

use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Model\BlockInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpCache\Esi;
use Symfony\Component\HttpKernel\Profiler\Profiler;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

class BlockRenderer extends AbstractRenderer
{
    protected CmsConfig $cmsConfig;
    protected Environment $twig;
    protected RouterInterface $router;
    protected bool $profilerEnabled;
    protected bool $esiEnabled;
    protected array $profilerDebugCollectorData = [];

    public function __construct(RequestStack $requestStack, CmsConfig $cmsConfig, Environment $twig, RouterInterface $router, ?Profiler $profiler, ?Esi $esi)
    {
        parent::__construct($requestStack);
        $this->cmsConfig = $cmsConfig;
        $this->twig = $twig;
        $this->router = $router;
        $this->profilerEnabled = (bool) $profiler;
        $this->esiEnabled = (bool) $esi;
    }

    public function renderBlockByType(string $type, array $params = []): string
    {
        $blockConfig = $this->cmsConfig->getBlock($type);

        if ($blockConfig['esi'] && !$this->isPreview()) {
            if (!$this->esiEnabled) {
                throw new \Exception('You must enable esi with framework.esi configuration to use it in CMS');
            }

            $renderFunction = 'render_esi';
        } else {
            $renderFunction = 'render';
        }

        if (!empty($blockConfig['render_url'])) {
            $params_string = implode(',', array_map(fn ($k, $v) => "'$k':'$v'", array_keys($params), array_values($params)));
            $twigCode = "{{ $renderFunction(url('{$blockConfig['render_url']}', {{$params_string}})) }}";
        } else {
            // $twigCode = "{{ $renderFunction(url('sfs_cms_block_render_by_type', {'type':'$type'})) }}";
            $params['type'] = $type;
            $params_string = implode(',', array_map(fn ($k, $v) => "'$k':'$v'", array_keys($params), array_values($params)));
            $twigCode = "{{ $renderFunction(controller('Softspring\\\\CmsBundle\\\\Controller\\\\BlockController::renderByType', {{$params_string}})) }}";
        }

        $template = twig_template_from_string($this->twig, $twigCode);

        if ($this->profilerEnabled) {
            $this->profilerDebugCollectorData[] = [
                'type' => $type,
                'config' => $blockConfig,
            ];
        }

        return $template->render();
    }

    public function renderBlock(BlockInterface $block): string
    {
        $type = $block->getType();
        $blockId = $block->getId();

        $blockConfig = $this->cmsConfig->getBlock($type);

        if ($blockConfig['esi'] && !$this->isPreview()) {
            if (!$this->esiEnabled) {
                throw new \Exception('You must enable esi with framework.esi configuration to use it in CMS');
            }

            $renderFunction = 'render_esi';
        } else {
            $renderFunction = 'render';
        }

        if (!empty($blockConfig['render_url'])) {
            $twigCode = "{{ $renderFunction(url('{$blockConfig['render_url']}')) }}";
        } else {
            // $twigCode = "{{ $renderFunction(url('sfs_cms_block_render_by_type', {'type':'$type'})) }}";
            $twigCode = "{{ $renderFunction(controller('Softspring\\\\CmsBundle\\\\Controller\\\\BlockController::renderById', {'id':'$blockId'})) }}";
        }

        $template = twig_template_from_string($this->twig, $twigCode);

        if ($this->profilerEnabled) {
            $this->profilerDebugCollectorData[] = [
                'type' => $type,
                'blockId' => $blockId,
                'config' => $blockConfig,
            ];
        }

        return $template->render();
    }

    public function getDebugCollectorData(): array
    {
        return $this->profilerDebugCollectorData;
    }
}
