<?php

namespace Softspring\CmsBundle\EntityListener;

use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Softspring\CmsBundle\EntityTransformer\UnsupportedException;
use Softspring\CmsBundle\EntityTransformer\VersionTransformer;
use Softspring\CmsBundle\Model\SectionVersionInterface;

class SectionVersionListener
{
    public function __construct(protected VersionTransformer $versionTransformer)
    {
    }

    /**
     * @throws UnsupportedException
     */
    public function postLoad(SectionVersionInterface $sectionVersion, PostLoadEventArgs $event): void
    {
        $this->versionTransformer->untransform($sectionVersion, $event->getObjectManager());
    }

    /**
     * @throws UnsupportedException
     */
    public function preUpdate(SectionVersionInterface $sectionVersion, PreUpdateEventArgs $event): void
    {
        $this->versionTransformer->transform($sectionVersion, $event->getObjectManager());
    }

    /**
     * @throws UnsupportedException
     */
    public function prePersist(SectionVersionInterface $sectionVersion, PrePersistEventArgs $event): void
    {
        $this->versionTransformer->transform($sectionVersion, $event->getObjectManager());
    }
}
