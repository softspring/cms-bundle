<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Test\Integration;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Softspring\CmsBundle\Entity\CompiledData;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

require_once __DIR__.'/CompiledCacheFixture.php';

final class ConcurrentCompiledCacheTest extends TestCase
{
    public function testSeparatePhpProcessesShareRedisLockAndOneMysqlCacheEntry(): void
    {
        $this->exercise(false);
    }

    public function testPublishedSectionsShareTheSameConcurrencyAndInvalidationRules(): void
    {
        if (!class_exists('Softspring\\CmsSectionsPlugin\\Entity\\SectionVersion')) {
            self::markTestSkipped('Sections plugin is optional.');
        }
        $this->exercise(true);
    }

    private function exercise(bool $section): void
    {
        if (!getenv('CMS_CACHE_TEST_DATABASE_URL') || !getenv('CMS_CACHE_TEST_REDIS_DSN') || !getenv('CMS_CACHE_TEST_AUTOLOAD')) {
            self::markTestSkipped('Explicit disposable MySQL/Redis test configuration required.');
        }
        $prefix = 'cms_cache_test_'.bin2hex(random_bytes(5));
        [$db, $store, $cache, $version] = CompiledCacheFixture::create($prefix, $section);
        if (!($db->getDatabasePlatform() instanceof AbstractMySQLPlatform)) {
            self::markTestSkipped('This regression test exercises real MySQL locking.');
        }
        $foreignKey = $section ? 'section_version_id' : 'content_version_id';
        $marker = tempnam(sys_get_temp_dir(), 'cms-compile-count-');
        $children = [];
        try {
            $db->executeStatement("CREATE TABLE {$prefix}_version (id VARCHAR(36) PRIMARY KEY, compiled_generation VARCHAR(36) DEFAULT '' NOT NULL) ENGINE=InnoDB");
            $db->insert($prefix.'_version', ['id' => $prefix]);
            $db->executeStatement("CREATE TABLE {$prefix}_compiled (id VARCHAR(36) PRIMARY KEY, content_version_id VARCHAR(36), section_version_id VARCHAR(36), compiled_key VARCHAR(50), data JSON, errors BOOLEAN, created_at INT, expires_at INT, generation VARCHAR(36) DEFAULT '', UNIQUE KEY context_key({$foreignKey}, compiled_key, generation)) ENGINE=InnoDB");
            for ($i = 0; $i < 6; ++$i) {
                $children[] = $process = new Process([PHP_BINARY, __DIR__.'/cache-worker.php', getenv('CMS_CACHE_TEST_AUTOLOAD'), $prefix, $marker, 'site/en', $section ? 'section' : 'content']);
                $process->start();
            }
            foreach ($children as $process) {
                $process->wait();
                self::assertSame(0, $process->getExitCode(), $process->getErrorOutput());
            }
            self::assertCount(1, file($marker, FILE_IGNORE_NEW_LINES));
            self::assertSame(1, (int) $db->fetchOne('SELECT COUNT(*) FROM '.$prefix.'_compiled'));
            // The editor invalidates while another process is rendering an uncached context.
            file_put_contents($marker, '');
            clearstatcache(true, $marker);
            $late = new Process([PHP_BINARY, __DIR__.'/cache-worker.php', getenv('CMS_CACHE_TEST_AUTOLOAD'), $prefix, $marker, 'site/fr', $section ? 'section' : 'content']);
            $children[] = $late;
            $late->start();
            $deadline = microtime(true) + 5;
            while (filesize($marker) === 0 && microtime(true) < $deadline) {
                usleep(10000);
                clearstatcache(true, $marker);
            }
            self::assertGreaterThan(0, filesize($marker), 'The late renderer did not start.');
            $version->cleanCompiled();
            $generation = $version->getCompiledGeneration();
            $db->update($prefix.'_version', ['compiled_generation' => $generation], ['id' => $prefix]);
            $late->wait();
            self::assertSame(0, $late->getExitCode(), $late->getErrorOutput());
            self::assertSame(0, (int) $db->fetchOne('SELECT COUNT(*) FROM '.$prefix.'_compiled WHERE compiled_key = ?', ['site/fr']));
            self::assertNull($store->find($version, 'site/en'), 'Old-generation HTML must not be served.');
            $fresh = new CompiledData();
            $fresh->setVersion($version);
            $fresh->setKey('site/en');
            $fresh->setDataPart('content', 'Fresh HTML');
            $store->save($fresh, $generation);
            self::assertSame('Fresh HTML', $store->find($version, 'site/en')->getDataPart('content'));
            // A caller-owned transaction must neither read nor publish shared cache values.
            $db->beginTransaction();
            self::assertSame($fresh, $cache->get($version, 'site/en', fn (): CompiledData => $fresh));
            $private = clone $fresh;
            $private->setDataPart('content', 'Uncommitted HTML');
            $store->save($private, $generation);
            self::assertTrue($db->isTransactionActive());
            $db->rollBack();
            self::assertSame('Fresh HTML', $store->find($version, 'site/en')->getDataPart('content'));

        } finally {
            foreach ($children as $process) {
                if ($process->isRunning()) $process->stop();
            }
            $db->executeStatement('DROP TABLE IF EXISTS '.$prefix.'_compiled');
            $db->executeStatement('DROP TABLE IF EXISTS '.$prefix.'_version');
            unlink($marker);
        }
    }
}
