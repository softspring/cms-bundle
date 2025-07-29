<?php

namespace Softspring\CmsBundle\Routing\Provider;

use Symfony\Component\Routing\RouteCollection;

class FrontendProvider implements RoutingProviderInterface
{
    public function supportedTypes(): array
    {
        return ['sfs_cms_plugin_frontend'];
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
