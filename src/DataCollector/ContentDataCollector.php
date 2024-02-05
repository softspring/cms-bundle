<?php

namespace Softspring\CmsBundle\DataCollector;

use Softspring\CmsBundle\Model\RouteInterface;
use Softspring\CmsBundle\Model\RoutePathInterface;
use Softspring\CmsBundle\Model\SiteInterface;
use Softspring\CmsBundle\Render\BlockRenderer;
use Softspring\CmsBundle\Render\ContentVersionRenderer;
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
    protected BlockRenderer $blockRenderer;
    protected MenuRenderer $menuRenderer;
    protected ContentVersionRenderer $contentRender;
    protected ?TranslatorInterface $translator;
    protected bool $profilerEnabled = false;
    protected bool $esiEnabled = false;
    protected bool $fragmentsEnabled = false;
    protected bool $httpCacheEnabled = false;

    public function __construct(BlockRenderer $blockRenderer, MenuRenderer $menuRenderer, ContentVersionRenderer $contentRender, ?TranslatorInterface $translator, ?Profiler $profiler, ?Esi $esi, ?FragmentListener $fragmentListener, ?HttpCache $httpCache)
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

    public function collect(Request $request, Response $response, ?\Throwable $exception = null): void
    {
        if (!$this->profilerEnabled) {
            return; // DO NOT COLLECT DATA IF PROFILER IS NOT ENABLED
        }

        if ('Softspring\CmsBundle\Controller\ContentController::renderRoutePath' !== $request->attributes->get('_controller')) {
            return;
        }

        $this->data['site'] = $request->attributes->get('_sfs_cms_site');
        $this->data['site_name'] = $this->translator->trans($this->data['site']->getId().'.name', [], 'sfs_cms_sites');
        $this->data['locale'] = $request->attributes->get('_sfs_cms_locale');

        /** @var RoutePathInterface $routePath */
        $routePath = $request->attributes->get('routePath');
        /** @var RouteInterface $route */
        $route = $routePath->getRoute();
        $this->data['route'] = [
            'id' => $route->getId(),
            'type' => $route->getType(),
            'parent' => $route->getParent()?->getId(),
            'content' => $route->getContent()?->getId(),
            'symfonyRoute' => $route->getSymfonyRoute(),
            'redirectUrl' => $route->getRedirectUrl(),
            'redirectType' => $route->getRedirectType(),
            'currentPath' => [
                'path' => $routePath->getPath(),
                'locale' => $routePath->getLocale(),
                'cacheTtl' => $routePath->getCacheTtl(),
                'compiledPath' => $routePath->getCompiledPath(),
            ],
            'paths' => array_map(function (RoutePathInterface $routePath) {
                return [
                    'path' => $routePath->getPath(),
                    'locale' => $routePath->getLocale(),
                    'cacheTtl' => $routePath->getCacheTtl(),
                    'compiledPath' => $routePath->getCompiledPath(),
                ];
            }, $route->getPaths()->toArray()),
        ];

        $content = $route->getContent();
        $this->data['content'] = [
            'id' => $content->getId(),
            'locales' => $content->getLocales(),
            'defaultLocale' => $content->getDefaultLocale(),
            'sites' => array_map(function (SiteInterface $site) {
                return [
                    'id' => $site->getId(),
                    'name' => $this->translator->trans($site->getId().'.name', [], 'sfs_cms_sites'),
                ];
            }, $content->getSites()->toArray()),
            'seo' => $content->getSeo(),
            'name' => $content->getName(),
            'lastVersionNumber' => $content->getLastVersionNumber(),
            'status' => $content->getStatus(),
            'extra' => $content->getExtraData(),
            'publishedVersion' => [
                'createdAt' => $content->getPublishedVersion()?->getCreatedAt(),
                'layout' => $content->getPublishedVersion()?->getLayout(),
            ],
        ];

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

    public function reset(): void
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
        return $this->data['site'] ?? null;
    }

    public function getLocale(): string
    {
        return $this->data['locale'] ?? '';
    }

    public function isCmsRequest(): bool
    {
        return isset($this->data['site_name']);
    }

    public function getRoute(): array
    {
        return $this->data['route'];
    }

    public function getContent(): array
    {
        return $this->data['content'];
    }

    public function getTitle(): ?string
    {
        $route = $this->getRoute();

        if (!$route) {
            return null;
        }

        switch ($route['type']) {
            case RouteInterface::TYPE_CONTENT:
                return $this->data['content']['name'];

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
