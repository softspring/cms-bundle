<?php

namespace Softspring\CmsBundle\EntityListener;

use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Entity\Content;

class ContentDiscriminatorMapListener implements EventSubscriberInterface
{
    protected CmsConfig $cmsConfig;

    public function __construct(CmsConfig $cmsConfig)
    {
        $this->cmsConfig = $cmsConfig;
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $metadata = $eventArgs->getClassMetadata();
        $class = $metadata->getReflectionClass();

        if (Content::class !== $class->getName()) {
            return;
        }

        foreach ($this->cmsConfig->getContentMappings() as $discriminator => $class) {
            $metadata->addDiscriminatorMapClass($discriminator, $class);
        }
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::loadClassMetadata,
        ];
    }
}