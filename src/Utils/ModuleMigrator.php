<?php

namespace Softspring\CmsBundle\Utils;

class ModuleMigrator
{
    public static function migrate(array $migrationScripts, $moduleData, $toRevision): array
    {
        foreach ($migrationScripts as $migrationScript) {
            $moduleData = (include $migrationScript)($moduleData, (int) ($moduleData['_revision'] ?? 1), $toRevision);
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
            'type' => !empty($symfonyRoute) ? 'route' : 'url',
            'route_name' => !empty($symfonyRoute) ? $symfonyRoute['route_name'] : null,
            'route_params' => !empty($symfonyRoute) ? $symfonyRoute['route_params'] : null,
            'url' => !empty($symfonyRoute) ? null : '',
            'anchor' => null,
            'target' => '_self',
            'custom_target' => null,
        ];
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
