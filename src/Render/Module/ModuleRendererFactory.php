<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Render\Module;

use Psr\Log\LoggerInterface;
use Softspring\CmsBundle\Config\CmsConfig;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;

class ModuleRendererFactory
{
    public function __construct(
        protected CmsConfig $cmsConfig,
        protected RequestStack $requestStack,
        protected ?LoggerInterface $cmsLogger,
    ) {
    }

    public function create(Environment $twig): ModuleRenderer
    {
        return new ModuleRenderer(
            $this->cmsConfig,
            $this->requestStack,
            $twig,
            $this->cmsLogger
        );
    }
}
