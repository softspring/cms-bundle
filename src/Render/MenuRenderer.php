<?php

namespace Softspring\CmsBundle\Render;

use Softspring\CmsBundle\Config\CmsConfig;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpCache\Esi;
use Twig\Environment;

class MenuRenderer extends AbstractRenderer
{
    protected CmsConfig $cmsConfig;
    protected Environment $twig;
    protected bool $esiEnabled;

    public function __construct(RequestStack $requestStack, CmsConfig $cmsConfig, Environment $twig, ?Esi $esi)
    {
        parent::__construct($requestStack);
        $this->cmsConfig = $cmsConfig;
        $this->twig = $twig;
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

        return $template->render();
    }
}
