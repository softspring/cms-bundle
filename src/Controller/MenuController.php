<?php

namespace Softspring\CmsBundle\Controller;

use Psr\Log\LoggerInterface;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Manager\MenuManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class MenuController extends AbstractController
{
    protected CmsConfig $cmsConfig;
    protected MenuManagerInterface $menuManager;
    protected Environment $twig;
    protected ?LoggerInterface $cmsLogger;

    public function __construct(CmsConfig $cmsConfig, MenuManagerInterface $menuManager, Environment $twig, ?LoggerInterface $cmsLogger)
    {
        $this->cmsConfig = $cmsConfig;
        $this->menuManager = $menuManager;
        $this->twig = $twig;
        $this->cmsLogger = $cmsLogger;
    }

    public function renderByType(string $type, Request $request): Response
    {
        try {
            $config = $this->cmsConfig->getMenu($type);

            $menu = $this->menuManager->getRepository()->findOneByType($type);

            if (!$menu) {
                $this->cmsLogger && $this->cmsLogger->error(sprintf('CMS missing menu %s', $type));

                return new Response();
            }

            $response = new Response($this->twig->render($config['render_template'], [
                'menu' => $menu,
            ]));

            if (false !== $config['cache_ttl'] && !$request->attributes->has('_cms_preview')) {
                $response->setPublic();
                $response->setMaxAge($config['cache_ttl']);
            }

            return $response;
        } catch (\Exception $e) {
            $this->cmsLogger && $this->cmsLogger->critical(sprintf('Error rendering menu %s: %s', $type, $e->getMessage()));

            return new Response('<!-- error rendering menu, see logs -->');
        }
    }
}
