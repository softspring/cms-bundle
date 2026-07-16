<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Serialization;

use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\Model\RouteInterface;
use Softspring\CmsBundle\Model\SiteInterface;

use const DATE_ATOM;

class PublishedContentSerializer
{
    public function summary(ContentInterface $content, string $contentType, ?string $locale = null): array
    {
        $version = $content->getPublishedVersion();

        return [
            'id' => $content->getId(),
            'type' => $contentType,
            'name' => $content->getName(),
            'defaultLocale' => $content->getDefaultLocale(),
            'locales' => $content->getLocales(),
            'sites' => array_map(static fn (SiteInterface $site): ?string => $site->getId(), $content->getSites()->toArray()),
            'publishedVersion' => $version instanceof ContentVersionInterface ? [
                'id' => $version->getId(),
                'versionNumber' => $version->getVersionNumber(),
                'layout' => $version->getLayout(),
                'createdAt' => $version->getCreatedAt()?->format(DATE_ATOM),
            ] : null,
            'routes' => $this->routes($content, $locale),
        ];
    }

    public function detail(ContentInterface $content, string $contentType, ContentVersionInterface $version, ?string $locale, bool $includeData): array
    {
        $detail = $this->summary($content, $contentType, $locale);
        $detail['extraData'] = $content->getExtraData();
        $detail['indexing'] = $content->getIndexing();
        $detail['publishedVersion']['seo'] = $version->getSeo();

        if ($includeData) {
            $detail['publishedVersion']['data'] = $version->getData();
        }

        return $detail;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function routes(ContentInterface $content, ?string $locale = null): array
    {
        return array_map(
            fn (RouteInterface $route): array => [
                'id' => $route->getId(),
                'type' => $route->getType(),
                'paths' => $this->routePaths($route, $locale),
            ],
            $content->getRoutes()->toArray(),
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function routePaths(RouteInterface $route, ?string $locale = null): array
    {
        $paths = [];

        foreach ($route->getPaths() as $path) {
            if ($locale && $path->getLocale() !== $locale) {
                continue;
            }

            $paths[] = [
                'id' => $path->getId(),
                'locale' => $path->getLocale(),
                'path' => $path->getPath(),
                'compiledPath' => $path->getCompiledPath(),
                'cacheTtl' => $path->getCacheTtl(),
                'sites' => array_map(static fn (SiteInterface $site): ?string => $site->getId(), $path->getSites()->toArray()),
            ];
        }

        return $paths;
    }
}
