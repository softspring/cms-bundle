<?php

namespace Softspring\CmsBundle\Test\Unit\Utils;

use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Utils\DataMigrator;

class DataMigratorTest extends TestCase
{
    public function testRouteToSymfonyRoute(): void
    {
        // test empty route
        $this->assertEquals(['route_name' => null, 'route_params' => []], DataMigrator::routeToSymfonyRoute(null));

        // test well-formed route
        $this->assertEquals(['route_name' => 'test', 'route_params' => []], DataMigrator::routeToSymfonyRoute('route___test'));

        // test bad-formed route
        $logErrors = ini_get('log_errors');
        $logFile = ini_get('error_log');
        ini_set("log_errors", 1);
        ini_set("error_log", '/tmp/module-migrator-test.log');
        $this->assertEquals(['route_name' => null, 'route_params' => []], DataMigrator::routeToSymfonyRoute('bad_formed_route'));
        ini_set("log_errors", $logErrors);
        ini_set("error_log", $logFile);
        $this->assertStringContainsString("Bad-formed 'bad_formed_route' route to migrate, has been set to null", file_get_contents('/tmp/module-migrator-test.log'));
    }
}