<?php

namespace Softspring\CmsBundle\Routing\Provider;

use Symfony\Component\Routing\RouteCollection;

interface RoutingProviderInterface
{
    public function supportedTypes(): array;

    public function supports(string $type): bool;

    public function getAdminRoutes(string $type): RouteCollection;
}
