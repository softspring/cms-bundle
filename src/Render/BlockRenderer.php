<?php

namespace Softspring\CmsBundle\Render;

use Softspring\CmsBundle\Config\CmsConfig;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpCache\Esi;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

class BlockRenderer extends AbstractRenderer
{
    protected CmsConfig $cmsConfig;
    protected Environment $twig;
    protected RouterInterface $router;
    protected bool $esiEnabled;

    public function __construct(RequestStack $requestStack, CmsConfig $cmsConfig, Environment $twig, RouterInterface $router, ?Esi $esi)
    {
        parent::__construct($requestStack);
        $this->cmsConfig = $cmsConfig;
        $this->twig = $twig;
        $this->router = $router;
        $this->esiEnabled = (bool) $esi;
    }

    public function renderBlockByType(string $type): string
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
            $twigCode = "{{ $renderFunction(url('{$blockConfig['render_url']}')) }}";
        } else {
            // $twigCode = "{{ $renderFunction(url('sfs_cms_block_render_by_type', {'type':'$type'})) }}";
            $twigCode = "{{ $renderFunction(controller('Softspring\\\\CmsBundle\\\\Controller\\\\BlockController::renderByType', {'type':'$type'})) }}";
        }

        $template = twig_template_from_string($this->twig, $twigCode);

        return $template->render();
    }
}