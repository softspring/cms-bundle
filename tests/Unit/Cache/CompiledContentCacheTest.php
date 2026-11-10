<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Test\Unit\Cache;

use ReflectionProperty;
use DateTime;
use RuntimeException;
use Softspring\CmsBundle\Model\CompiledDataInterface;
use Doctrine\DBAL\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Cache\CompiledContentCache;
use Softspring\CmsBundle\Cache\CompiledDataStoreInterface;
use Softspring\CmsBundle\Compiler\CompileException;
use Softspring\CmsBundle\Entity\CompiledData;
use Softspring\CmsBundle\Entity\ContentVersion;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\SharedLockInterface;

class CompiledContentCacheTest extends TestCase
{
    private function version(): ContentVersion
    {
        $version = new ContentVersion();
        (new ReflectionProperty(ContentVersion::class, 'id'))->setValue($version, 'version-1');

        return $version;
    }

    private function value(ContentVersion $version, string $html = 'Rendered'): CompiledData
    {
        $compiled = new CompiledData();
        $compiled->setVersion($version);
        $compiled->setKey('site/en');
        $compiled->setDataPart('content', $html);

        return $compiled;
    }

    public function testWarmHitDoesNotLockCompileOrWrite(): void
    {
        $version = $this->version();
        $cached = $this->value($version);
        $store = $this->createMock(CompiledDataStoreInterface::class);
        $store->method('find')->willReturn($cached);
        $store->expects(self::never())->method('save');
        $factory = $this->createMock(LockFactory::class);
        $factory->expects(self::never())->method('createLock');
        self::assertSame($cached, (new CompiledContentCache($store, $factory))->get($version, 'site/en', function (): void { self::fail('Warm hit compiled'); }));
    }

    public function testLockIsFollowedByAnotherReadAndTheWinnerIsReused(): void
    {
        $version = $this->version();
        $winner = $this->value($version, 'Other request');
        $store = $this->createMock(CompiledDataStoreInterface::class);
        $store->method('find')->willReturnOnConsecutiveCalls(null, $winner);
        $store->expects(self::never())->method('save');
        $lock = $this->createMock(SharedLockInterface::class);
        $lock->expects(self::once())->method('acquire')->with(false)->willReturn(true);
        $lock->expects(self::once())->method('release');
        $factory = $this->createMock(LockFactory::class);
        $factory->method('createLock')->willReturn($lock);
        self::assertSame($winner, (new CompiledContentCache($store, $factory))->get($version, 'site/en', function (): void { self::fail('Duplicated compilation'); }));
    }

    public function testBusyLockTimesOutToUncachedRendering(): void
    {
        $version = $this->version();
        $value = $this->value($version);
        $store = $this->createMock(CompiledDataStoreInterface::class);
        $store->method('find')->willReturn(null);
        $store->expects(self::never())->method('save');
        $lock = $this->createMock(SharedLockInterface::class);
        $lock->expects(self::once())->method('acquire')->with(false)->willReturn(false);
        $lock->expects(self::never())->method('release');
        $factory = $this->createMock(LockFactory::class);
        $factory->method('createLock')->willReturn($lock);
        self::assertSame($value, (new CompiledContentCache($store, $factory, null, 0))->get($version, 'site/en', fn (): CompiledData => $value));
    }

    public function testNestedBusyLockNeverWaitsAndOnlyTheOuterEntryIsStored(): void
    {
        $version = $this->version();
        $outer = $this->value($version, 'Outer');
        $inner = $this->value($version, 'Inner');
        $store = $this->createMock(CompiledDataStoreInterface::class);
        $store->method('find')->willReturn(null);
        $store->expects(self::once())->method('save')->with($outer);
        $held = $this->createMock(SharedLockInterface::class);
        $held->method('acquire')->willReturn(true);
        $held->method('isAcquired')->willReturn(true);
        $held->expects(self::once())->method('release');
        $busy = $this->createMock(SharedLockInterface::class);
        $busy->expects(self::once())->method('acquire')->with(false)->willReturn(false);
        $factory = $this->createMock(LockFactory::class);
        $factory->method('createLock')->willReturnOnConsecutiveCalls($held, $busy);
        $cache = new CompiledContentCache($store, $factory, null, 20);
        self::assertSame($outer, $cache->get($version, 'outer', function () use ($cache, $version, $inner, $outer): CompiledData {
            self::assertSame($inner, $cache->get($version, 'inner', fn (): CompiledData => $inner));

            return $outer;
        }));
    }

    public function testCacheWriteFailureDoesNotLoseAlreadyRenderedHtml(): void
    {
        $version = $this->version();
        $value = $this->value($version);
        $store = $this->createMock(CompiledDataStoreInterface::class);
        $store->method('find')->willReturn(null);
        $store->method('save')->willThrowException(new InvalidArgumentException('Cache storage unavailable'));
        self::assertSame($value, (new CompiledContentCache($store))->get($version, 'site/en', fn (): CompiledData => $value));
    }

    public function testExpiredAndErrorEntriesAreNotServedAndCompilationErrorsAreNotStored(): void
    {
        $version = $this->version();
        $old = $this->value($version, 'Expired');
        $old->setExpiresAt(new DateTime('yesterday'));
        $invalid = $this->value($version, 'Partial error output');
        $invalid->setErrors(true);
        $store = $this->createMock(CompiledDataStoreInterface::class);
        $store->method('find')->willReturn($old);
        $store->expects(self::never())->method('save');
        self::assertSame($invalid, (new CompiledContentCache($store))->get($version, 'site/en', fn (): CompiledData => $invalid));
    }

    public function testRendererFailureReleasesLockAndPropagates(): void
    {
        $store = $this->createMock(CompiledDataStoreInterface::class);
        $store->method('find')->willReturn(null);
        $lock = $this->createMock(SharedLockInterface::class);
        $lock->method('acquire')->willReturn(true);
        $lock->expects(self::once())->method('release');
        $factory = $this->createMock(LockFactory::class);
        $factory->method('createLock')->willReturn($lock);
        $this->expectException(CompileException::class);
        (new CompiledContentCache($store, $factory))->get($this->version(), 'site/en', function (): void { throw new CompileException('Broken template'); });
    }

    public function testUnavailableLockFactoryRendersWithoutPublishingCache(): void
    {
        $version = $this->version();
        $value = $this->value($version);
        $store = $this->createMock(CompiledDataStoreInterface::class);
        $store->method('find')->willReturn(null);
        $store->expects(self::never())->method('save');
        $factory = $this->createMock(LockFactory::class);
        $factory->method('createLock')->willThrowException(new RuntimeException('Lock backend unavailable'));
        self::assertSame($value, (new CompiledContentCache($store, $factory))->get($version, 'site/en', fn (): CompiledData => $value));
    }

    public function testRecursiveCompilationFailsAndCoordinatorCanBeReused(): void
    {
        $version = $this->version();
        $value = $this->value($version);
        $store = $this->createMock(CompiledDataStoreInterface::class);
        $store->method('find')->willReturn(null);
        $store->expects(self::once())->method('save')->with($value);
        $cache = new CompiledContentCache($store);
        try {
            $cache->get($version, 'site/en', fn (): CompiledDataInterface => $cache->get($version, 'site/en', fn (): CompiledData => $value));
            self::fail('Recursive compilation was accepted.');
        } catch (CompileException $e) {
            self::assertStringContainsString('Recursive compilation', $e->getMessage());
        }
        self::assertSame($value, $cache->get($version, 'site/en', fn (): CompiledData => $value));
    }
}
