<?php

namespace Softspring\CmsBundle\Routing\Provider;

use Symfony\Component\Routing\RouteCollection;

class AdminSiteProvider implements RoutingProviderInterface
{
    public function supportedTypes(): array
    {
        return ['sfs_cms_plugin_admin_site'];
    }

    public function supports(string $type): bool
    {
        return in_array($type, $this->supportedTypes(), true);
    }

    public function getAdminRoutes(string $type): RouteCollection
    {
        return new RouteCollection();
    }
}
