<?php

namespace Softspring\CmsBundle\Utils;

use Softspring\CmsBundle\Config\CmsConfig;

/**
 * @deprecated renamed to DataMigrator, will be removed in 6.0
 */
class ModuleMigrator
{
    public static function migrate(array $migrationScripts, $moduleData, $toRevision, ?CmsConfig $cmsConfig = null): array
    {
        trigger_deprecation('softspring/cms-bundle', '5.1', 'ModuleMigrator is deprecated, use DataMigrator instead.');

        return DataMigrator::migrate($migrationScripts, $moduleData, $toRevision, $cmsConfig);
    }

    public static function routeToSymfonyRoute(?string $oldRouteValue): array
    {
        trigger_deprecation('softspring/cms-bundle', '5.1', 'ModuleMigrator is deprecated, use DataMigrator instead.');

        return DataMigrator::routeToSymfonyRoute($oldRouteValue);
    }

    public static function symfonyRouteToLink(?array $symfonyRoute): array
    {
        trigger_deprecation('softspring/cms-bundle', '5.1', 'ModuleMigrator is deprecated, use DataMigrator instead.');

        return DataMigrator::symfonyRouteToLink($symfonyRoute);
    }
}
