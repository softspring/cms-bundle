<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Cache;

use Softspring\CmsBundle\Model\CompiledDataInterface;
use Softspring\CmsBundle\Model\VersionInterface;

/** Derived data storage; implementations must never flush the rendering EntityManager. */
interface CompiledDataStoreInterface
{
    public function find(VersionInterface $version, string $key): ?CompiledDataInterface;

    public function save(CompiledDataInterface $compiled, string $generation): void;

    public function isTransactionActive(): bool;
}
