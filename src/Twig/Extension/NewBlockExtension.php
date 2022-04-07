<?php

namespace Softspring\CmsBundle\Twig\Extension;

use Softspring\CmsBundle\Config\CmsConfig;
use Symfony\Component\HttpKernel\HttpCache\Esi;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class NewBlockExtension extends AbstractExtension
{
    protected CmsConfig $cmsConfig;

    protected Environment $twig;

    protected RouterInterface $router;

    protected bool $esiEnabled;

    public function __construct(CmsConfig $cmsConfig, Environment $twig, RouterInterface $router, ?Esi $esi)
    {
        $this->cmsConfig = $cmsConfig;
        $this->twig = $twig;
        $this->router = $router;
        $this->esiEnabled = (bool) $esi;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('sfs_cms_block', [$this, 'renderBlockByType'], ['is_safe' => ['html']]),
        ];
    }

    public function renderBlockByType(string $type): string
    {
        $blockConfig = $this->cmsConfig->getBlock($type);

        if ($blockConfig['esi']) {
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