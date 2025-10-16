<?php

namespace Softspring\CmsBundle\Manager;

use Softspring\CmsBundle\Model\CompiledDataInterface;
use Softspring\CmsBundle\Model\SiteInterface;
use Softspring\CmsBundle\Model\VersionInterface;
use Softspring\Component\CrudlController\Manager\CrudlEntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

interface CompiledDataManagerInterface extends CrudlEntityManagerInterface
{
    /**
     * @return CompiledDataInterface
     */
    public function createEntity(): object;

    /**
     * @psalm-param CompiledDataInterface $entity
     */
    public function saveEntity(object $entity): void;

    /**
     * @psalm-param CompiledDataInterface $entity
     */
    public function deleteEntity(object $entity): void;

    public function getCompileKeyFromRequest(VersionInterface $version, Request $request): string;

    public function getCompileKey(VersionInterface $version, string $locale, ?SiteInterface $site = null): string;
}
