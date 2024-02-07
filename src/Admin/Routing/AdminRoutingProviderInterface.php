<?php

namespace Softspring\CmsBundle\Admin\Routing;

use Symfony\Component\Routing\RouteCollection;

interface AdminRoutingProviderInterface
{
    public function getAdminRoutes(string $type): RouteCollection;
}
