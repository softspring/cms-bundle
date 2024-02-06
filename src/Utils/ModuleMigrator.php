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

    public static function classToSpacing(string $class): array
    {
        $spacing = [
            'marginTop' => '',
            'marginEnd' => '',
            'marginBottom' => '',
            'marginStart' => '',
            'paddingTop' => '',
            'paddingEnd' => '',
            'paddingBottom' => '',
            'paddingStart' => '',
        ];

        preg_match_all('/([mp][tbesxy]?\-[012345])/', $class, $matches);

        foreach ($matches[0] as $match) {
            $class = str_replace($match, '', $class);

            $match = explode('-', $match);
            $marginOrPadding = substr($match[0], 0, 1);
            $p = substr($match[0], 1, 1);
            $amount = $match[1];

            $mp = ['m' => 'margin', 'p' => 'padding'][$marginOrPadding];
            $positions = [
                't' => ['Top'],
                'b' => ['Bottom'],
                'e' => ['End'],
                's' => ['Start'],
                '' => ['Top', 'End', 'Bottom', 'Start'],
                'y' => ['Top', 'Bottom'],
                'x' => ['End', 'Start'],
            ][$p];

            foreach ($positions as $position) {
                $p = strtolower(substr($position, 0, 1));
                $spacing["$mp$position"] = "$marginOrPadding$p-$amount";
            }
        }

        $class = implode(' ', array_filter(explode(' ', $class)));

        return [$class, $spacing];
    }
}
