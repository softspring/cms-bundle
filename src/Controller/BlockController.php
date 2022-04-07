<?php

namespace Softspring\CmsBundle\Controller;

use Monolog\Logger;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Manager\BlockManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class BlockController extends AbstractController
{
    protected CmsConfig $cmsConfig;
    protected BlockManagerInterface $blockManager;
    protected ?Logger $cmsLogger;

    public function __construct(CmsConfig $cmsConfig, BlockManagerInterface $blockManager, ?Logger $cmsLogger)
    {
        $this->cmsConfig = $cmsConfig;
        $this->blockManager = $blockManager;
        $this->cmsLogger = $cmsLogger;
    }

    public function renderByType(string $type): Response
    {
        $config = $this->cmsConfig->getBlock($type);

        if (!$config['static']) {
            $block = $this->blockManager->getRepository()->findOneByType($type);

            if (!$block) {
                $this->cmsLogger && $this->cmsLogger->error(sprintf('CMS missing block %s', $type));
                return new Response();
            }

            $response = $this->render($config['render_template'], [
                'block' => $block,
            ]);
        } else {
            $response = $this->render($config['render_template']);
        }

        if ($config['cache_ttl'] !== false) {
            $response->setPublic();
            $response->setMaxAge($config['cache_ttl']);
        }

        return $response;
    }
}