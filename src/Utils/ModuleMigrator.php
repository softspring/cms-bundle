<?php

namespace Softspring\CmsBundle\Utils;

class ModuleMigrator
{
    public static function migrate(array $migrationScripts, $moduleData, $toRevision): array
    {
        foreach ($migrationScripts as $migrationScript) {
            $moduleData = (include $migrationScript)($moduleData, (int) ($moduleData['_revision'] ?? 1), $toRevision);
        }

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
}
