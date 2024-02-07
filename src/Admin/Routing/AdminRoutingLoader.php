<?php

namespace Softspring\CmsBundle\Admin\Routing;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\RouteCollection;

class AdminRoutingLoader extends Loader
{
    public function __construct(?string $env = null, protected iterable $adminRoutingProviders = [])
    {
        parent::__construct($env);
    }

    public function load($resource, ?string $type = null): RouteCollection
    {
        $collection = new RouteCollection();

        foreach ($this->adminRoutingProviders as $adminRoutingProvider) {
            $collection->addCollection($adminRoutingProvider->getAdminRoutes($type));
        }

        return $collection;
    }

    public function supports($resource, ?string $type = null): bool
    {
        // in the future more types could be added
        return in_array($type, ['sfs_cms_plugin_admin_content_type']);
    }
}
