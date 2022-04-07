<?php

namespace Softspring\CmsBundle\Twig\Extension;

use Softspring\CmsBundle\Config\CmsConfig;
use Symfony\Component\HttpKernel\HttpCache\Esi;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class MenuExtension extends AbstractExtension
{
    protected CmsConfig $cmsConfig;

    protected Environment $twig;

    protected bool $esiEnabled;

    public function __construct(CmsConfig $cmsConfig, Environment $twig, ?Esi $esi)
    {
        $this->cmsConfig = $cmsConfig;
        $this->twig = $twig;
        $this->esiEnabled = (bool) $esi;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('sfs_cms_menu', [$this, 'renderMenuByType'], ['is_safe' => ['html']]),
        ];
    }

    public function renderMenuByType(string $type): string
    {
        $menuConfig = $this->cmsConfig->getMenu($type);

        if ($menuConfig['esi']) {
            if (!$this->esiEnabled) {
                throw new \Exception('You must enable esi with framework.esi configuration to use it in CMS');
            }

            $renderFunction = 'render_esi';
        } else {
            $renderFunction = 'render';
        }

        $twigCode = "{{ $renderFunction(controller('Softspring\\\\CmsBundle\\\\Controller\\\\MenuController::renderByType', {'type':'$type'})) }}";

        $template = twig_template_from_string($this->twig, $twigCode);

        return $template->render();
    }
}