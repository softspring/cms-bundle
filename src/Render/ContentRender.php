<?php

namespace Softspring\CmsBundle\Render;

use Psr\Log\LoggerInterface;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Twig\Environment;

class ContentRender
{
    protected Environment $twig;

    protected CmsConfig $cmsConfig;

    protected ?LoggerInterface $cmsLogger;

    public function __construct(Environment $twig, CmsConfig $cmsConfig, ?LoggerInterface $cmsLogger)
    {
        $this->twig = $twig;
        $this->cmsConfig = $cmsConfig;
        $this->cmsLogger = $cmsLogger;
    }

    public function render(ContentVersionInterface $version): string
    {
        $this->cmsLogger && $this->cmsLogger->debug(sprintf('Rendering %s page %s version', $version->getContent()->getName(), $version->getId()));

        $containers = [];
        $layout = $this->cmsConfig->getLayout($version->getLayout());
        $versionData = $version->getData();

        foreach ($layout['containers'] as $layoutContainerId => $layoutContainerConfig) {
            $layoutContainer = $versionData ? $versionData[$layoutContainerId] : [];
            $containers[$layoutContainerId] = '';

            foreach ($layoutContainer as $module) {
                $containers[$layoutContainerId] .= $this->renderModule($module, $version);
            }
        }

        return $this->twig->render($layout['render_template'], [
            'containers' => $containers,
            'version' => $version,
            'content' => $version->getContent(),
        ]);
    }

    protected function renderModule(array $module, ContentVersionInterface $version): string
    {
        $this->cmsLogger && $this->cmsLogger->debug(sprintf('Rendering %s module', $module['_type']));

        if ($module['_type'] == 'container') {
            $module['content'] = '';

            foreach ($module['modules'] as $submodule) {
                $module['content'] .= $this->renderModule($submodule, $version);
            }

            return $this->twig->render($this->cmsConfig->getModule($module['_type'])['render_template'], $module);
        }

        if ($module['_type'] == 'row') {
            $module['content'] = '';

            foreach ($module['modules'] as $submodule) {
                $module['content'] .= $this->renderModule($submodule, $version);
            }

            return $this->twig->render($this->cmsConfig->getModule($module['_type'])['render_template'], $module);
        }

        $module += [
            'version' => $version,
            'content' => $version->getContent(),
        ];

        return $this->twig->render($this->cmsConfig->getModule($module['_type'])['render_template'], $module);
    }
}
