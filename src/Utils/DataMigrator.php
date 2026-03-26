<?php

namespace Softspring\CmsBundle\Utils;

use Softspring\CmsBundle\Config\CmsConfig;

class DataMigrator
{
    public static function migrate(array $migrationScripts, $moduleData, $toRevision, ?CmsConfig $cmsConfig = null): array
    {
        foreach ($migrationScripts as $migrationScript) {
            $moduleData = (include $migrationScript)($moduleData, (int) ($moduleData['_revision'] ?? 1), $toRevision, $cmsConfig);
        }

        $moduleData['_revision'] = $toRevision;

        return $moduleData;
    }

    public static function routeToSymfonyRoute(?string $oldRouteValue): array
    {
        if (is_string($oldRouteValue) && '' !== $oldRouteValue && 'route___' !== substr($oldRouteValue, 0, 8)) {
            error_log("Bad-formed '$oldRouteValue' route to migrate, has been set to null.");
            $oldRouteValue = null;
        }

        return [
            // migrate from route___<route_name> format
            'route_name' => $oldRouteValue ? substr($oldRouteValue, 8) : null,
            'route_params' => [],
        ];
    }

    public static function symfonyRouteToLink(?array $symfonyRoute): array
    {
        return [
            'type' => null === $symfonyRoute || [] === $symfonyRoute ? 'url' : 'route',
            'route_name' => null === $symfonyRoute || [] === $symfonyRoute ? null : $symfonyRoute['route_name'] ?? null,
            'route_params' => null === $symfonyRoute || [] === $symfonyRoute ? null : $symfonyRoute['route_params'] ?? null,
            'url' => null === $symfonyRoute || [] === $symfonyRoute ? '' : null,
            'anchor' => null,
            'target' => '_self',
            'custom_target' => null,
        ];
    }
}
