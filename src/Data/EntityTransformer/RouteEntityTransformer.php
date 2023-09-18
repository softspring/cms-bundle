<?php

namespace Softspring\CmsBundle\Data\EntityTransformer;

use Softspring\CmsBundle\Data\Exception\InvalidElementException;
use Softspring\CmsBundle\Data\Exception\ReferenceNotFoundException;
use Softspring\CmsBundle\Data\Exception\RunPreloadBeforeImportException;
use Softspring\CmsBundle\Data\ReferencesRepository;
use Softspring\CmsBundle\Manager\RouteManagerInterface;
use Softspring\CmsBundle\Manager\RoutePathManagerInterface;
use Softspring\CmsBundle\Manager\SiteManagerInterface;
use Softspring\CmsBundle\Model\RouteInterface;
use Softspring\CmsBundle\Model\SiteInterface;
use Softspring\CmsBundle\Utils\Slugger;

class RouteEntityTransformer implements EntityTransformerInterface
{
    protected RouteManagerInterface $routeManager;
    protected RoutePathManagerInterface $routePathManager;
    protected SiteManagerInterface $siteManager;

    public function __construct(RouteManagerInterface $routeManager, RoutePathManagerInterface $routePathManager, SiteManagerInterface $siteManager)
    {
        $this->routeManager = $routeManager;
        $this->routePathManager = $routePathManager;
        $this->siteManager = $siteManager;
    }

    public static function getPriority(): int
    {
        return 0;
    }

    public function supports(string $type, $data = null): bool
    {
        if ('routes' === $type) {
            return true;
        }

        return false;
    }

    public function export(object $element, &$files = []): array
    {
        if (!$element instanceof RouteInterface) {
            throw new InvalidElementException(sprintf('%s dumper requires that $element to be an instance of %s, %s given.', get_called_class(), RouteInterface::class, get_class($element)));
        }
        $route = $element;

        $dump = [
            'route' => [
                'id' => $route->getId(),
                'sites' => $route->getSites()->map(fn(SiteInterface $site) => $site->getId())->toArray(),
                'type' => $route->getType(),
                'parent' => $route->getParent()?->getId(),
                'symfony_route' => $route->getSymfonyRoute(),
                'content' => null,
                'redirect_type' => $route->getRedirectType(),
                'redirect_url' => $route->getRedirectUrl(),
                'paths' => [],
            ],
        ];

        if ($route->getContent()) {
            $dump['route']['content'] = Slugger::lowerSlug($route->getContent()->getName());
        }

        foreach ($route->getPaths() as $path) {
            $dump['route']['paths'][] = [
                'path' => $path->getPath(),
                'locale' => $path->getLocale(),
                'cache_ttl' => $path->getCacheTtl(),
            ];
        }

        return $dump;
    }

    public function preload(array $data, ReferencesRepository $referencesRepository): void
    {
        $referencesRepository->addReference("route___{$data['route']['id']}", $this->routeManager->createEntity());
    }

    public function import(array $data, ReferencesRepository $referencesRepository, array $options = []): RouteInterface
    {
        $routeData = $data['route'];

        try {
            /** @var RouteInterface $route */
            $route = $referencesRepository->getReference("route___{$routeData['id']}", true);
        } catch (ReferenceNotFoundException $e) {
            throw new RunPreloadBeforeImportException('You must call preload() method before importing', 0, $e);
        }

        $route->setId($routeData['id']);

        // @deprecated: FIX OLD FIXTURES
        $routeData['sites'] = isset($routeData['site']) ? [$routeData['site']] : $routeData['sites'];

        foreach ($routeData['sites'] as $site) {
            $route->addSite($referencesRepository->getReference("site___{$site}", true));
        }

        $route->setType(RouteInterface::TYPE_UNKNOWN);

        if ($routeData['parent'] ?? false) {
            $route->setParent($referencesRepository->getReference("route___{$routeData['parent']}", true));
        }

        switch ($routeData['type']) {
            case RouteInterface::TYPE_CONTENT:
                $route->setContent($referencesRepository->getReference("content___{$routeData['content']}", true));
                break;

            case RouteInterface::TYPE_REDIRECT_TO_URL:
                $route->setRedirectUrl($routeData['redirect_url']);
                $route->setRedirectType($routeData['redirect_type']);
                break;

            case RouteInterface::TYPE_REDIRECT_TO_ROUTE:
                $route->setSymfonyRoute($routeData['symfony_route']);
                break;

            case RouteInterface::TYPE_PARENT_ROUTE:
                // nothing to do
                break;

            default:
                throw new \Exception(sprintf('Route type %u not yet implemented', $routeData['type']));
        }

        // store valid type
        $route->setType($routeData['type']);

        foreach ($route->getPaths() as $existingPath) {
            $route->removePath($existingPath);
        }

        foreach ($routeData['paths'] as $paths) {
            $route->addPath($routePath = $this->routePathManager->createEntity());
            $routePath->setPath($paths['path']);
            $routePath->setLocale($paths['locale'] ?? null);
            $routePath->setCacheTtl($paths['cache_ttl'] ?? null);
        }

        return $route;
    }
}
