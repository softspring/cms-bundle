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
}
