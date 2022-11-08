<?php

namespace Softspring\CmsBundle\Dumper\Model;

use Softspring\CmsBundle\Dumper\Exception\InvalidElementException;
use Softspring\CmsBundle\Dumper\Utils\Slugger;
use Softspring\CmsBundle\Model\RouteInterface;

class RouteDumper extends AbstractDumper
{
    public static function dump(object $element, &$files = []): array
    {
        if (!$element instanceof RouteInterface) {
            throw new InvalidElementException(sprintf('%s dumper requires that $element to be an instance of %s, %s given.', get_called_class(), RouteInterface::class, get_class($element)));
        }
        $route = $element;

        $dump = [
            'route' => [
                'id' => $route->getId(),
                'site' => $route->getSite(),
                'type' => $route->getType(),
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
}
