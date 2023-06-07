<?php

namespace Softspring\CmsBundle\Tests;

use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Utils\ModuleMigrator;

abstract class ModuleTestCase extends TestCase
{
    protected string $modulePath = '';

    protected function assertMigrateTest(array $originData, array $expectedData): void
    {
        $migratedData = ModuleMigrator::migrate(["{$this->modulePath}/migrate.php"], $originData, $expectedData['_revision']);
        $this->assertEquals($expectedData, $migratedData);
    }

    abstract protected function provideDataForMigrations(): array;

    public function testMigrations(): void
    {
        $revisions = $this->provideDataForMigrations();

        foreach ($revisions as $originRevision) {
            foreach ($revisions as $targetRevision) if ($originRevision['_revision'] < $targetRevision['_revision']) {
                $this->assertMigrateTest($originRevision, $targetRevision);
            }
        }
    }
}
