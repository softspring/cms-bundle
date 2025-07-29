<?php

namespace Softspring\CmsBundle\Routing\Provider;

use Symfony\Component\Routing\RouteCollection;

class AdminProvider implements RoutingProviderInterface
{
    public function supportedTypes(): array
    {
        return ['sfs_cms_plugin_admin'];
    }

    public function supports(string $type): bool
    {
        return in_array($type, $this->supportedTypes());
    }

    public function getAdminRoutes(string $type): RouteCollection
    {
        return new RouteCollection();
    }
}
