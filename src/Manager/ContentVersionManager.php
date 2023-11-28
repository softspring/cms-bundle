<?php

namespace Softspring\CmsBundle\Manager;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\Component\CrudlController\Manager\CrudlEntityManagerTrait;

class ContentVersionManager implements ContentVersionManagerInterface
{
    use CrudlEntityManagerTrait;

    protected EntityManagerInterface $em;
    protected CmsConfig $cmsConfig;
    protected bool $saveCompiled;

    public function __construct(EntityManagerInterface $em, CmsConfig $cmsConfig, bool $saveCompiled)
    {
        $this->em = $em;
        $this->cmsConfig = $cmsConfig;
        $this->saveCompiled = $saveCompiled;
    }

    public function getTargetClass(): string
    {
        return ContentVersionInterface::class;
    }

    public function getLatestVersions(ContentInterface $content, int $limit = 3): Collection
    {
        return new ArrayCollection($this->getRepository()->createQueryBuilder('cv')
            ->where('cv.content = :content')
            ->setParameter('content', $content)
            ->orderBy('cv.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult());
    }

    public function canSaveCompiled(ContentVersionInterface $version): bool
    {
        if (false === $this->saveCompiled) {
            return false;
        }

        $contentConfig = $this->cmsConfig->getContent($version->getContent());

        if (false === $contentConfig['save_compiled']) {
            return false;
        }

        $layoutConfig = $this->cmsConfig->getLayout($version->getLayout());

        if (false === $layoutConfig['save_compiled']) {
            return false;
        }

        return true;
    }
}
