<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Cache;

use DateTime;
use Doctrine\DBAL\Exception as DatabaseException;
use Exception;
use Psr\Log\LoggerInterface;
use Softspring\CmsBundle\Compiler\CompileException;
use Softspring\CmsBundle\Model\CompilableInterface;
use Softspring\CmsBundle\Model\CompiledDataInterface;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\Model\VersionInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\LockInterface;

/** Serialize cold-cache renders without holding a SQL transaction during rendering. */
class CompiledContentCache
{
    private int $depth = 0;
    private array $compiling = [];

    public function __construct(
        private CompiledDataStoreInterface $store,
        private ?LockFactory $lockFactory = null,
        private ?LoggerInterface $cmsLogger = null,
        private float $lockWaitSeconds = 2.0,
        private float $lockTtlSeconds = 300.0,
    ) {
    }

    public function get(VersionInterface $version, string $key, callable $compile): CompiledDataInterface
    {
        if (!$version instanceof CompilableInterface || !$version->getId() || $this->store->isTransactionActive()) {
            return $compile();
        }
        $generation = $version->getCompiledGeneration();
        if (($cached = $this->find($version, $key)) instanceof CompiledDataInterface) {
            return $cached;
        }
        $resource = 'sfs-cms-compile:'.hash('sha256', json_encode([$version instanceof ContentVersionInterface ? 'content' : 'section', (string) $version->getId(), $key, $generation], JSON_THROW_ON_ERROR));
        if (isset($this->compiling[$resource])) {
            throw new CompileException('Recursive compilation of the same version and context.');
        }

        $lock = null;
        $acquired = false;
        $acquisitionFailed = false;
        $maySave = !$this->lockFactory instanceof LockFactory;
        try {
            if ($this->lockFactory instanceof LockFactory) {
                try {
                    $lock = $this->lockFactory->createLock($resource, $this->lockTtlSeconds, false);
                    // Nested renders never wait: A -> B and B -> A must not deadlock.
                    $deadline = microtime(true) + ($this->depth > 0 ? 0 : max(0, $this->lockWaitSeconds));
                    $delay = 20000;
                    while (true) {
                        if ($acquired = $lock->acquire(false)) {
                            break;
                        }
                        if (($cached = $this->find($version, $key)) instanceof CompiledDataInterface) {
                            return $cached;
                        }
                        if (microtime(true) >= $deadline) {
                            break;
                        }
                        usleep(min($delay, max(0, (int) (($deadline - microtime(true)) * 1000000))));
                        $delay = min(200000, $delay * 2);
                    }
                    $maySave = $acquired;
                } catch (Exception $e) {
                    $acquisitionFailed = true;
                    $this->cmsLogger?->warning('CMS compilation lock unavailable; rendering without caching.', ['exception_class' => get_class($e)]);
                }
            }
            // Another instance may have completed the entry before we acquired the lock.
            if (($cached = $this->find($version, $key)) instanceof CompiledDataInterface) {
                return $cached;
            }
            ++$this->depth;
            $this->compiling[$resource] = true;
            try {
                $compiled = $compile();
            } finally {
                --$this->depth;
                unset($this->compiling[$resource]);
            }
            if ($maySave && !$compiled->hasErrors() && null !== $compiled->getDataPart('content')) {
                if ($this->ownsLease($lock)) {
                    try {
                        $this->store->save($compiled, $generation);
                    } catch (DatabaseException $e) {
                        $this->cmsLogger?->warning('CMS compiled cache write failed; returning rendered content.', ['exception_class' => get_class($e)]);
                    }
                }
            }

            return $compiled;
        } finally {
            if ($lock instanceof LockInterface && ($acquired || $acquisitionFailed)) {
                try {
                    $lock->release();
                } catch (Exception $e) {
                    $this->cmsLogger?->warning('CMS compilation lock release failed.', ['exception_class' => get_class($e)]);
                }
            }
        }
    }

    private function ownsLease(?LockInterface $lock): bool
    {
        if (!$lock instanceof LockInterface) {
            return true;
        }
        try {
            return $lock->isAcquired();
        } catch (Exception $e) {
            $this->cmsLogger?->warning('CMS compilation lease check failed; returning rendered content.', ['exception_class' => get_class($e)]);

            return false;
        }
    }

    /** @phpstan-impure Shared cache state can change between reads. */
    private function find(VersionInterface $version, string $key): ?CompiledDataInterface
    {
        try {
            $cached = $this->store->find($version, $key);
            if (!$cached?->getDataPart('content') || $cached->hasErrors()
                || ($cached->getExpiresAt() instanceof DateTime && $cached->getExpiresAt()->getTimestamp() <= time())) {
                return null;
            }

            return $cached;
        } catch (DatabaseException $e) {
            $this->cmsLogger?->warning('CMS compiled cache read failed; rendering without cached content.', ['exception_class' => get_class($e)]);

            return null;
        }
    }
}
