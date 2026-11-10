<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Test\Integration;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Redis;
use RedisException;
use Symfony\Component\Lock\Store\StoreFactory;
use ReflectionProperty;
use Symfony\Component\Uid\Uuid;
use Softspring\CmsBundle\Model\CompiledDataInterface;
use Softspring\CmsBundle\Model\VersionInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Cache\CompiledContentCache;
use Softspring\CmsBundle\Entity\CompiledData;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\RedisStore;
use Symfony\Component\Process\Process;

require_once __DIR__.'/CompiledCacheFixture.php';

final class CompiledCacheStressTest extends TestCase
{
    private string $prefix;
    private string $directory;
    private array $fixture;
    private array $children = [];

    public static function modes(): array
    {
        return ['content/redis' => [false, true], 'content/no-locks' => [false, false], 'sections/redis' => [true, true], 'sections/no-locks' => [true, false]];
    }

    private function prepare(bool $section, bool $locks): void
    {
        if (!getenv('CMS_CACHE_TEST_DATABASE_URL') || !getenv('CMS_CACHE_TEST_AUTOLOAD') || ($locks && !getenv('CMS_CACHE_TEST_REDIS_DSN'))) {
            self::markTestSkipped('Explicit disposable MySQL/Redis test configuration required.');
        }
        if ($section && !class_exists('Softspring\\CmsSectionsPlugin\\Entity\\SectionVersion')) self::markTestSkipped('Optional sections plugin missing.');
        $this->prefix = 'cms_stress_'.bin2hex(random_bytes(5));
        $this->fixture = CompiledCacheFixture::create($this->prefix, $section, $locks);
        $db = $this->fixture[0];
        if (!$db->getDatabasePlatform() instanceof AbstractMySQLPlatform) self::markTestSkipped('MySQL required.');
        $this->directory = sys_get_temp_dir().'/'.$this->prefix;
        mkdir($this->directory);
        $p = $this->prefix;
        $fk = $section ? 'section_version_id' : 'content_version_id';
        $db->executeStatement("CREATE TABLE {$p}_version (id VARCHAR(36) PRIMARY KEY, compiled_generation VARCHAR(36) DEFAULT '' NOT NULL) ENGINE=InnoDB");
        $db->insert($p.'_version', ['id' => $p]);
        $db->executeStatement("CREATE TABLE {$p}_compiled (id VARCHAR(36) PRIMARY KEY, content_version_id VARCHAR(36), section_version_id VARCHAR(36), compiled_key VARCHAR(50), data JSON, errors BOOLEAN, created_at INT, expires_at INT, generation VARCHAR(36) DEFAULT '', UNIQUE KEY context_key({$fk}, compiled_key, generation), FOREIGN KEY ({$fk}) REFERENCES {$p}_version(id)) ENGINE=InnoDB");
    }

    protected function tearDown(): void
    {
        foreach ($this->children as $child) if ($child->isRunning()) $child->stop();
        if (isset($this->directory)) {
            $db = $this->fixture[0];
            $db->executeStatement('DROP TABLE IF EXISTS '.$this->prefix.'_compiled');
            $db->executeStatement('DROP TABLE IF EXISTS '.$this->prefix.'_version');
            foreach (glob($this->directory.'/*') as $file) unlink($file);
            rmdir($this->directory);
        }
    }

    #[DataProvider('modes')]
    public function testRepeatedConcurrentColdAndWarmBursts(bool $section, bool $locks): void
    {
        $this->prepare($section, $locks);
        [$db] = $this->fixture;
        $contexts = ['site/en', 'site/de', 'site/es', 'other/en'];
        for ($wave = 0; $wave < 3; ++$wave) {
            $db->executeStatement('DELETE FROM '.$this->prefix.'_compiled');
            $marker = $this->directory.'/count-'.$wave;
            file_put_contents($marker, '');
            $start = $this->directory.'/start-'.$wave;
            $batch = [];
            for ($i = 0; $i < 16; ++$i) {
                $key = $wave === 1 ? $contexts[$i % 4] : 'site/en';
                $batch[] = $process = new Process([PHP_BINARY, __DIR__.'/cache-worker.php', getenv('CMS_CACHE_TEST_AUTOLOAD'), $this->prefix, $marker, $key, $section ? 'section' : 'content', $locks ? 'redis' : 'none', '25', $start], null, $locks ? null : ['CMS_CACHE_TEST_REDIS_DSN' => false]);
                $this->children[] = $process;
                $process->setTimeout(30);
                $process->start();
            }
            $deadline = microtime(true) + 15;
            while (count(glob($start.'.ready.*')) < 16 && microtime(true) < $deadline) usleep(10000);
            self::assertCount(16, glob($start.'.ready.*'), 'Not all processes reached the start barrier.');
            touch($start);
            foreach ($batch as $process) {
                $process->wait();
                self::assertSame(0, $process->getExitCode(), $process->getErrorOutput());
            }
            self::assertFalse(is_file($marker.'.errors'), is_file($marker.'.errors') ? file_get_contents($marker.'.errors') : '');
            $expected = $wave === 1 ? 4 : 1;
            self::assertSame($expected, (int) $db->fetchOne('SELECT COUNT(*) FROM '.$this->prefix.'_compiled'));
            $count = count(file($marker, FILE_IGNORE_NEW_LINES));
            if ($locks) self::assertSame($expected, $count, 'Redis should coalesce the cold burst.');
            else self::assertGreaterThanOrEqual($expected, $count);
        }
    }

    public function testRedisConnectionFailsAfterAcquisitionWithoutLosingHtml(): void
    {
        $this->prepare(false, true);
        [, $store, , $version] = $this->fixture;
        $dsn = parse_url(getenv('CMS_CACHE_TEST_REDIS_DSN'));
        $real = new Redis();
        $real->connect($dsn['host'], $dsn['port'] ?? 6379);
        $connectionState = new class { public bool $failed = false; public int $failures = 0; };
        $proxy = $this->createStub(Redis::class);
        $proxy->method('clearLastError')->willReturnCallback(fn (): bool => $real->clearLastError());
        $proxy->method('getLastError')->willReturnCallback(fn (): ?string => $real->getLastError());
        foreach (['eval', 'evalSha', 'script'] as $method) {
            $proxy->method($method)->willReturnCallback(function (...$args) use ($real, $connectionState, $method) {
                if ($connectionState->failed) {
                    ++$connectionState->failures;
                    throw new RedisException('Injected Redis disconnect during render');
                }

                return $real->$method(...$args);
            });
        }
        $cache = new CompiledContentCache($store, new LockFactory(new RedisStore($proxy)), null, 0, 0.1);
        $value = $this->value($version);
        self::assertSame($value, $cache->get($version, 'site/en', function () use ($value, $connectionState): CompiledData { $connectionState->failed = true; return $value; }));
        self::assertGreaterThan(0, $connectionState->failures, 'Redis did not fail after acquiring the lock.');
        self::assertNull($store->find($version, 'site/en'));
        // The failed release is fenced by a short TTL, not a throwing destructor.
        $connectionState->failed = false;
        usleep(200000);
        self::assertSame($value, $cache->get($version, 'site/en', fn (): CompiledData => $value));
        self::assertSame('Rendered', $this->cached($store, $version)->getDataPart('content'));
    }

    public function testSqlLockTimeoutRetriesThenReturnsHtmlAndRecovers(): void
    {
        $this->prepare(false, false);
        [$db, $store, $cache, $version] = $this->fixture;
        [$blocker] = CompiledCacheFixture::create($this->prefix, false, false);
        $blocker->beginTransaction();
        try {
            $blocker->fetchOne('SELECT id FROM '.$this->prefix.'_version WHERE id = ? FOR UPDATE', [$this->prefix]);
            $db->executeStatement('SET SESSION innodb_lock_wait_timeout = 1');
            $value = $this->value($version);
            self::assertSame($value, $cache->get($version, 'site/en', fn (): CompiledData => $value));
            self::assertFalse($db->isTransactionActive());
            self::assertNull($store->find($version, 'site/en'));
        } finally {
            $blocker->rollBack();
        }
        $cache->get($version, 'site/en', fn (): CompiledData => $value);
        self::assertSame('Rendered', $this->cached($store, $version)->getDataPart('content'));
    }

    public function testExpiredRedisOwnerCannotOverwriteItsReplacement(): void
    {
        $this->prepare(false, true);
        [, $store, $replacement, $version] = $this->fixture;
        $factory = new LockFactory(StoreFactory::createStore(getenv('CMS_CACHE_TEST_REDIS_DSN')));
        $old = new CompiledContentCache($store, $factory, null, 0, 0.05);
        $winner = $this->value($version);
        $winner->setDataPart('content', 'Replacement');
        $expired = $this->value($version);
        $expired->setDataPart('content', 'Old owner');
        self::assertSame($expired, $old->get($version, 'site/en', function () use ($replacement, $version, $winner, $expired): CompiledData {
            usleep(100000);
            self::assertSame($winner, $replacement->get($version, 'site/en', fn (): CompiledData => $winner));
            return $expired;
        }));
        self::assertSame('Replacement', $this->cached($store, $version)->getDataPart('content'));
    }

    public function testBusyRedisOwnerIsNotOverwrittenByUncachedFallback(): void
    {
        $this->prepare(false, true);
        [, $store, $owner, $version] = $this->fixture;
        $factory = new LockFactory(StoreFactory::createStore(getenv('CMS_CACHE_TEST_REDIS_DSN')));
        $loser = new CompiledContentCache($store, $factory, null, 0);
        $first = $this->value($version);
        $second = $this->value($version);
        $second->setDataPart('content', 'Uncached alternative');
        $owner->get($version, 'site/en', function () use ($loser, $store, $version, $first, $second): CompiledData {
            self::assertSame($second, $loser->get($version, 'site/en', fn (): CompiledData => $second));
            self::assertNull($store->find($version, 'site/en'));
            return $first;
        });
        self::assertSame('Rendered', $this->cached($store, $version)->getDataPart('content'));
    }

    public function testMissingSqlCacheTableDoesNotLoseHtmlAndCanRecover(): void
    {
        $this->prepare(false, false);
        [$db, $store, $cache, $version] = $this->fixture;
        $ddl = $db->fetchAssociative('SHOW CREATE TABLE '.$this->prefix.'_compiled')['Create Table'];
        $db->executeStatement('DROP TABLE '.$this->prefix.'_compiled');
        $value = $this->value($version);
        self::assertSame($value, $cache->get($version, 'site/en', fn (): CompiledData => $value));
        self::assertFalse($db->isTransactionActive());
        $db->executeStatement($ddl);
        $cache->get($version, 'site/en', fn (): CompiledData => $value);
        self::assertSame('Rendered', $this->cached($store, $version)->getDataPart('content'));
    }

    #[DataProvider('modes')]
    public function testRepeatedInvalidationsDuringConcurrentReaders(bool $section, bool $locks): void
    {
        $this->prepare($section, $locks);
        [$db, $store, $cache, $version] = $this->fixture;
        $marker = $this->directory.'/churn';
        file_put_contents($marker, '');
        $batch = [];
        for ($i = 0; $i < 8; ++$i) {
            $batch[] = $process = new Process([PHP_BINARY, __DIR__.'/cache-worker.php', getenv('CMS_CACHE_TEST_AUTOLOAD'), $this->prefix, $marker, 'site/en', $section ? 'section' : 'content', $locks ? 'churn' : 'churn-none', '120'], null, $locks ? null : ['CMS_CACHE_TEST_REDIS_DSN' => false]);
            $this->children[] = $process;
            $process->setTimeout(30);
            $process->start();
        }
        for ($i = 0; $i < 20; ++$i) {
            usleep(30000);
            $version->cleanCompiled();
            $db->update($this->prefix.'_version', ['compiled_generation' => $version->getCompiledGeneration()], ['id' => $this->prefix]);
        }
        foreach ($batch as $process) {
            $process->wait();
            self::assertSame(0, $process->getExitCode(), $process->getErrorOutput());
        }
        self::assertFalse(is_file($marker.'.errors'), is_file($marker.'.errors') ? file_get_contents($marker.'.errors') : '');
        $value = $this->value($version);
        $cache->get($version, 'site/en', fn (): CompiledData => $value);
        $row = $store->find($version, 'site/en');
        self::assertNotNull($row);
        self::assertSame($version->getCompiledGeneration(), $row->getGeneration());
        self::assertSame(1, (int) $db->fetchOne('SELECT COUNT(*) FROM '.$this->prefix.'_compiled'));
    }

    #[DataProvider('modes')]
    public function testEditorialDeleteBeforeGenerationUpdateSurvivesConcurrentCacheWriter(bool $section, bool $locks): void
    {
        $this->prepare($section, $locks);
        [$db, $store, $cache, $version] = $this->fixture;
        [$editor] = CompiledCacheFixture::create($this->prefix, $section, false);

        for ($round = 0; $round < 3; ++$round) {
            $store->save($this->value($version), $version->getCompiledGeneration());
            $db->executeStatement('UPDATE '.$this->prefix.'_compiled SET expires_at = 1');
            $marker = $this->directory.'/editorial-'.$round;
            file_put_contents($marker, '');
            // Match ORM collection deletion before the version UPDATE. This is
            // deliberately the inverse of the cache writer's locking order.
            $editor->beginTransaction();
            try {
                $editor->executeStatement('DELETE FROM '.$this->prefix.'_compiled');
                $process = new Process([PHP_BINARY, __DIR__.'/cache-worker.php', getenv('CMS_CACHE_TEST_AUTOLOAD'), $this->prefix, $marker, 'site/en', $section ? 'section' : 'content', $locks ? 'redis' : 'none']);
                $this->children[] = $process;
                $process->setTimeout(15);
                $process->start();

                $blockedWriter = false;
                $deadline = microtime(true) + 5;
                do {
                    foreach ($db->fetchAllAssociative('SHOW FULL PROCESSLIST') as $query) {
                        if (str_starts_with($query['Info'] ?? '', 'UPDATE '.$this->prefix.'_compiled')) {
                            $blockedWriter = true;
                            break 2;
                        }
                    }
                    usleep(10000);
                } while ($process->isRunning() && microtime(true) < $deadline);
                self::assertTrue($blockedWriter, 'Cache writer never reached the conflicting row lock.');

                $version->cleanCompiled();
                $editor->update($this->prefix.'_version', ['compiled_generation' => $version->getCompiledGeneration()], ['id' => $this->prefix]);
                $editor->commit();
            } finally {
                if ($editor->isTransactionActive()) $editor->rollBack();
            }
            $process->wait();
            self::assertSame(0, $process->getExitCode(), $process->getErrorOutput());
            self::assertFalse(is_file($marker.'.errors'), is_file($marker.'.errors') ? file_get_contents($marker.'.errors') : '');
            self::assertNull($store->find($version, 'site/en'), 'A stale renderer repopulated invalidated HTML.');
            $cache->get($version, 'site/en', fn (): CompiledData => $this->value($version));
            self::assertSame($version->getCompiledGeneration(), $this->cached($store, $version)->getGeneration());
        }
    }

    public static function editorialCollections(): array
    {
        return ['content/loaded' => [false, false], 'content/stale' => [false, true], 'sections/loaded' => [true, false], 'sections/stale' => [true, true]];
    }

    #[DataProvider('editorialCollections')]
    public function testOrmRecompilationReplacesLoadedOrStaleCollections(bool $section, bool $stale): void
    {
        $this->prepare($section, false);
        [$db, $store, , $version, $em] = $this->fixture;
        $managed = $em->find(get_class($version), $this->prefix);
        if ($stale) iterator_to_array($managed->getCompiled());
        $store->save($this->value($version), $version->getCompiledGeneration());
        if (!$stale) iterator_to_array($managed->getCompiled());

        for ($round = 0; $round < 2; ++$round) {
            $managed->cleanCompiled();
            $fresh = $this->value($managed);
            $fresh->setGeneration($managed->getCompiledGeneration());
            $fresh->setDataPart('content', 'Editorial '.$round);
            (new ReflectionProperty($fresh, 'id'))->setValue($fresh, (string) Uuid::v7());
            $managed->addCompiled($fresh);
            $em->flush();

            self::assertTrue($em->isOpen());
            self::assertSame('Editorial '.$round, $store->find($managed, 'site/en')->getDataPart('content'));
            self::assertSame(1, (int) $db->fetchOne('SELECT COUNT(*) FROM '.$this->prefix.'_compiled WHERE generation = ?', [$managed->getCompiledGeneration()]));
        }
        // Obsolete rows may survive an already initialized ORM collection.
        // They must not be picked in preference to the current cache row.
        $db->executeStatement('UPDATE '.$this->prefix.'_compiled SET created_at = ? WHERE generation <> ?', [time() + 1000, $managed->getCompiledGeneration()]);
        $currentId = $store->find($managed, 'site/en')->getId();
        $store->save($this->value($managed), $managed->getCompiledGeneration());
        self::assertSame($currentId, $store->find($managed, 'site/en')->getId());
        self::assertSame(1, (int) $db->fetchOne('SELECT COUNT(*) FROM '.$this->prefix.'_compiled WHERE generation = ?', [$managed->getCompiledGeneration()]));
    }

    private function cached($store, $version): CompiledDataInterface
    {
        $cached = $store->find($version, 'site/en');
        self::assertNotNull($cached);

        return $cached;
    }

    private function value(?VersionInterface $version): CompiledData
    {
        $value = new CompiledData();
        $value->setVersion($version);
        $value->setKey('site/en');
        $value->setDataPart('content', 'Rendered');
        return $value;
    }
}
