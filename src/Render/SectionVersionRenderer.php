<?php

namespace Softspring\CmsBundle\Render;

use Softspring\CmsBundle\Model\SectionVersionInterface;
use Softspring\CmsBundle\Render\Error\RenderErrorList;
use Softspring\CmsBundle\Render\Exception\RenderException;
use Softspring\CmsBundle\Render\Isolated\IsolatedRunner;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;

class SectionVersionRenderer
{
    protected array $profilerDebugCollectorData = [];

    public function __construct(
        protected IsolatedRunner $isolatedRunner,
    ) {
    }

    /**
     * @throws RenderException
     */
    public function render(SectionVersionInterface $version, Request $request, ?RenderErrorList $renderErrorList = null): string
    {
        return $this->isolatedRunner->isolateRequestRender($request, function (Request $request, Environment $twig, ModuleRenderer $moduleRenderer) use ($version, $renderErrorList): string {
            // preload all medias
            $version->getMedias();
            // preload all routes
            $version->getRoutes();

            $versionData = $version->getData();

            $renderErrorList && $renderErrorList->resetLocation();
            $renderErrorList && $renderErrorList->pushLocation('data');

            $section = '';
            foreach ($versionData as $moduleData) {
                $section .= $moduleRenderer->render($moduleData, $this->profilerDebugCollectorData, [], $renderErrorList);
            }

            return $section;
        });
    }

    public function getDebugCollectorData(): array
    {
        return $this->profilerDebugCollectorData;
    }
}
