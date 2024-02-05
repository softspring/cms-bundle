<?php

namespace Softspring\CmsBundle\DataCollector;

use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\RouteInterface;
use Softspring\CmsBundle\Model\RoutePathInterface;
use Softspring\CmsBundle\Model\SiteInterface;
use Softspring\CmsBundle\Render\BlockRenderer;
use Softspring\CmsBundle\Render\ContentRender;
use Softspring\CmsBundle\Render\MenuRenderer;
use Symfony\Bundle\FrameworkBundle\HttpCache\HttpCache;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpKernel\EventListener\FragmentListener;
use Symfony\Component\HttpKernel\HttpCache\Esi;
use Symfony\Component\HttpKernel\Profiler\Profiler;
use Symfony\Contracts\Translation\TranslatorInterface;

class ContentDataCollector extends DataCollector
{
    protected $data = [];

    protected BlockRenderer $blockRenderer;
    protected MenuRenderer $menuRenderer;
    protected ContentRender $contentRender;
    protected ?TranslatorInterface $translator;
    protected bool $profilerEnabled = false;
    protected bool $esiEnabled = false;
    protected bool $fragmentsEnabled = false;
    protected bool $httpCacheEnabled = false;

    public function __construct(BlockRenderer $blockRenderer, MenuRenderer $menuRenderer, ContentRender $contentRender, ?TranslatorInterface $translator, ?Profiler $profiler, ?Esi $esi, ?FragmentListener $fragmentListener, ?HttpCache $httpCache)
    {
        $this->blockRenderer = $blockRenderer;
        $this->menuRenderer = $menuRenderer;
        $this->contentRender = $contentRender;
        $this->translator = $translator;
        $this->profilerEnabled = (bool) $profiler;
        $this->esiEnabled = (bool) $esi;
        $this->fragmentsEnabled = (bool) $fragmentListener;
        $this->httpCacheEnabled = (bool) $httpCache;
    }

    public function collect(Request $request, Response $response, ?\Throwable $exception = null)
    {
        if (!$this->profilerEnabled) {
            return; // DO NOT COLLECT DATA IF PROFILER IS NOT ENABLED
        }

        if ('Softspring\CmsBundle\Controller\ContentController::renderRoutePath' !== $request->attributes->get('_controller')) {
            return;
        }

        $this->data['_sfs_cms_site'] = $request->attributes->get('_sfs_cms_site');
        $this->data['site_name'] = $this->translator->trans($this->data['_sfs_cms_site']->getId().'.name', [], 'sfs_cms_sites');
        $this->data['_sfs_cms_locale'] = $request->attributes->get('_sfs_cms_locale');
        $this->data['_sfs_cms_locale_path'] = $request->attributes->get('_sfs_cms_locale_path');
        $this->data['_route'] = $request->attributes->get('_route');
        $this->data['_route_params'] = $request->attributes->get('_route_params');
        $this->data['_controller'] = $request->attributes->get('_controller');
        $this->data['routePath'] = $request->attributes->get('routePath');

        $this->data['cache'] = array_intersect_key($response->headers->all(), array_flip(['cache-control', 'date']));

        $this->data['modules'] = $this->contentRender->getDebugCollectorData();
        $this->data['blocks'] = $this->blockRenderer->getDebugCollectorData();
        $this->data['menus'] = $this->menuRenderer->getDebugCollectorData();

        $this->data['esiEnabled'] = $this->esiEnabled;
        $this->data['fragmentsEnabled'] = $this->fragmentsEnabled;
        $this->data['httpCacheEnabled'] = $this->httpCacheEnabled;
    }

    public function getName(): string
    {
        return 'cms';
    }

    public function reset()
    {
    }

    public function getBlocks(): array
    {
        return $this->data['blocks'] ?? [];
    }

    public function getModules(): array
    {
        return $this->data['modules'] ?? [];
    }

    public function getMenus(): array
    {
        return $this->data['menus'] ?? [];
    }

    public function getCache(): array
    {
        return $this->data['cache'] ?? [];
    }

    public function getSiteName(): string
    {
        return $this->data['site_name'] ?? '';
    }

    public function getSite(): ?SiteInterface
    {
        return $this->data['_sfs_cms_site'] ?? null;
    }

    public function getLocale(): string
    {
        return $this->data['_sfs_cms_locale'] ?? '';
    }

    public function isCmsRequest(): bool
    {
        return isset($this->data['site_name']);
    }

    public function getRoutePath(): ?RoutePathInterface
    {
        if (empty($this->data['routePath']) || !$this->data['routePath'] instanceof RoutePathInterface) {
            return null;
        }

        return $this->data['routePath'];
    }

    public function getRoute(): ?RouteInterface
    {
        return $this->getRoutePath()->getRoute() ?? null;
    }

    public function getContent(): ?ContentInterface
    {
        $route = $this->getRoute();

        if (!$route || RouteInterface::TYPE_CONTENT !== $route->getType()) {
            return null;
        }

        return $route->getContent();
    }

    public function getTitle(): ?string
    {
        $route = $this->getRoute();

        if (!$route) {
            return null;
        }

        switch ($route->getType()) {
            case RouteInterface::TYPE_CONTENT:
                return $route->getContent()->getName();

            default:
                return 'no yet implemented';
        }
    }

    public function isEsiEnabled(): bool
    {
        return $this->data['esiEnabled'];
    }

    public function isFragmentsEnabled(): bool
    {
        return $this->data['fragmentsEnabled'];
    }

    public function isHttpCacheEnabled(): bool
    {
        return $this->data['httpCacheEnabled'];
    }
}
