<?php

namespace Softspring\CmsBundle\Routing\Loader;

use Softspring\CmsBundle\Routing\Provider\RoutingProviderInterface;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\RouteCollection;

class RoutingLoader extends Loader
{
    public function __construct(?string $env = null, protected iterable $routingProviders = [])
    {
        parent::__construct($env);
    }

    public function load(mixed $resource, ?string $type = null): RouteCollection
    {
        $collection = new RouteCollection();

        /** @var RoutingProviderInterface $routingProvider */
        foreach ($this->routingProviders as $routingProvider) {
            if (!$routingProvider->supports($type)) {
                continue;
            }
            $collection->addCollection($routingProvider->getAdminRoutes($type));
        }

        return $collection;
    }

    public function supports(mixed $resource, ?string $type = null): bool
    {
        $types = [];
        foreach ($this->routingProviders as $routingProvider) {
            $types = array_merge($types, $routingProvider->supportedTypes());
        }

        return in_array($type, $types);
    }
}
