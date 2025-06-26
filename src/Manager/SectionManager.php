<?php

namespace Softspring\CmsBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Softspring\CmsBundle\Helper\CmsHelper;
use Softspring\CmsBundle\Model\SectionInterface;
use Softspring\CmsBundle\Model\SectionVersionInterface;
use Softspring\CmsBundle\Model\VersionInterface;
use Softspring\Component\CrudlController\Manager\CrudlEntityManagerTrait;

class SectionManager implements SectionManagerInterface
{
    use CrudlEntityManagerTrait;

    public function __construct(
        protected EntityManagerInterface $em,
        protected SectionVersionManagerInterface $sectionVersionManager,
        protected CmsHelper $cmsHelper,
        protected RouteManagerInterface $routeManager,
        protected iterable $entityDuplicators,
    ) {
    }

    public function getTargetClass(): string
    {
        return SectionInterface::class;
    }

    public function duplicateEntity(SectionInterface $section): SectionInterface
    {
        $newSection = $this->createEntity(false);
        $newSection->setName($section->getName().' (copy)');

        foreach ($this->entityDuplicators as $duplicator) {
            if ($duplicator->supports($section)) {
                $duplicator->duplicateData($section, $newSection);
            }
        }

        return $newSection;
    }

    public function createEntity(bool $addEmptyVersion = true): object
    {
        $class = $this->getEntityClass();

        /** @var SectionInterface $section */
        $section = new $class();
        if ($addEmptyVersion) {
            $section->addVersion($version = $this->sectionVersionManager->createEntity());
            $section->setLastVersion($version);
            $section->setLastVersionNumber(0);
            $version->setVersionNumber(0);
        }

        return $section;
    }

    public function getRepository(): EntityRepository
    {
        return $this->em->getRepository($this->getEntityClass());
    }

    public function createVersion(SectionInterface $section, ?SectionVersionInterface $prevVersion = null, ?int $origin = VersionInterface::ORIGIN_UNKNOWN): SectionVersionInterface
    {
        $version = $this->sectionVersionManager->createEntity();

        if (!$prevVersion && $section->getLastVersion()) {
            $prevVersion = $section->getLastVersion();
        }

        if ($prevVersion) {
            $version->setData($prevVersion->getData());
        }

        $section->setLastVersionNumber((int) $section->getLastVersionNumber() + 1);
        $version->setVersionNumber($section->getLastVersionNumber());
        $version->setOrigin($origin);

        $section->addVersion($version);
        $section->setLastVersion($version);

        return $version;
    }
}
