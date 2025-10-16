<?php

namespace Softspring\CmsBundle\Manager;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Softspring\CmsBundle\Model\CompiledDataInterface;
use Softspring\CmsBundle\Model\SiteInterface;
use Softspring\CmsBundle\Model\VersionInterface;
use Softspring\Component\CrudlController\Manager\CrudlEntityManagerTrait;
use Symfony\Component\HttpFoundation\Request;

class CompiledDataManager implements CompiledDataManagerInterface
{
    use CrudlEntityManagerTrait;

    public function __construct(
        protected EntityManagerInterface $em,
        protected string $prefixCompiled,
        protected ?int $compiledDataExpirationTtl = null,
    ) {
    }

    public function getTargetClass(): string
    {
        return CompiledDataInterface::class;
    }

    public function createEntity(): object
    {
        $class = $this->getEntityClass();
        /** @var CompiledDataInterface $entity */
        $entity = new $class();

        if ($this->compiledDataExpirationTtl) {
            $entity->setExpiresAt(new DateTime('now +'.$this->compiledDataExpirationTtl.' seconds'));
        }

        return $entity;
    }

    public function getCompileKeyFromRequest(VersionInterface $version, Request $request): string
    {
        return $this->getCompileKey($version, $request->getLocale(), $request->attributes->get('_sfs_cms_site'));
    }

    public function getCompileKey(VersionInterface $version, string $locale, ?SiteInterface $site = null): string
    {
        return "{$this->prefixCompiled}{$site}/{$locale}";
    }
}
