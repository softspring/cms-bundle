<?php

namespace Softspring\CmsBundle\Controller;

use Monolog\Logger;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Manager\MenuManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MenuController extends AbstractController
{
    protected CmsConfig $cmsConfig;
    protected MenuManagerInterface $menuManager;
    protected ?Logger $cmsLogger;

    public function __construct(CmsConfig $cmsConfig, MenuManagerInterface $menuManager, ?Logger $cmsLogger)
    {
        $this->cmsConfig = $cmsConfig;
        $this->menuManager = $menuManager;
        $this->cmsLogger = $cmsLogger;
    }

    public function renderByType(string $type, Request $request): Response
    {
        $config = $this->cmsConfig->getMenu($type);

        $menu = $this->menuManager->getRepository()->findOneByType($type);

        if (!$menu) {
            $this->cmsLogger && $this->cmsLogger->error(sprintf('CMS missing menu %s', $type));

            return new Response();
        }

        $response = $this->render($config['render_template'], [
            'menu' => $menu,
        ]);

        if (false !== $config['cache_ttl'] && !$request->attributes->has('_cms_preview')) {
            $response->setPublic();
            $response->setMaxAge($config['cache_ttl']);
        }

        return $response;
    }
}
