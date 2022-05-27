<?php

namespace Softspring\CmsBundle\Render;

use Psr\Log\LoggerInterface;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Form\Module\ContainerModuleType;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;

class ContentRender
{
    protected Environment $twig;

    protected CmsConfig $cmsConfig;

    protected RequestStack $requestStack;

    protected ?LoggerInterface $cmsLogger;

    public function __construct(Environment $twig, CmsConfig $cmsConfig, RequestStack $requestStack, ?LoggerInterface $cmsLogger)
    {
        $this->twig = $twig;
        $this->cmsConfig = $cmsConfig;
        $this->requestStack = $requestStack;
        $this->cmsLogger = $cmsLogger;
    }

    public function render(ContentVersionInterface $version): string
    {
        $this->cmsLogger && $this->cmsLogger->debug(sprintf('Rendering %s page version', $version->getContent()->getName()));

        $layout = $this->cmsConfig->getLayout($version->getLayout());

        $containers = $version->getCompiledModules()[$this->requestStack->getCurrentRequest()->getLocale()] ?? $this->renderModules($version);

        return $this->twig->render($layout['render_template'], [
            'containers' => $containers,
            'version' => $version,
            'content' => $version->getContent(),
        ]);
    }

    public function renderModules(ContentVersionInterface $version): array
    {
        $layout = $this->cmsConfig->getLayout($version->getLayout());
        $versionData = $version->getData();

        $containers = [];
        foreach ($layout['containers'] as $layoutContainerId => $layoutContainerConfig) {
            $layoutContainer = $versionData ? $versionData[$layoutContainerId]??[] : [];
            $containers[$layoutContainerId] = '';

            foreach ($layoutContainer as $module) {
                $containers[$layoutContainerId] .= $this->renderModule($module, $version);
            }
        }

        return $containers;
    }

    protected function renderModule(array $module, ContentVersionInterface $version): string
    {
        $this->cmsLogger && $this->cmsLogger->debug(sprintf('Rendering %s module', $module['_module']));

        if ($this->isContainer($module)) {
            $module['content'] = '';

            foreach ($module['modules'] as $submodule) {
                $module['content'] .= $this->renderModule($submodule, $version);
            }

            return $this->twig->render($this->cmsConfig->getModule($module['_module'])['render_template'], $module);
        }

        $module += [
            'version' => $version,
            'content' => $version->getContent(),
        ];

        return $this->twig->render($this->cmsConfig->getModule($module['_module'])['render_template'], $module);
    }

    private function isContainer($module)
    {
        $module = $this->cmsConfig->getModule($module['_module']);

        return ContainerModuleType::class === $module['module_type'];
    }
}
