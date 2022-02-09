<?php

namespace Softspring\CmsBundle\EntityListener;

use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Softspring\CmsBundle\Entity\AbstractModule;

class ModuleDiscriminatorMapListener implements EventSubscriberInterface
{
    protected array $modulesMappings;

    public function __construct(array $modulesMappings)
    {
        $this->modulesMappings = $modulesMappings;
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $metadata = $eventArgs->getClassMetadata();
        $class = $metadata->getReflectionClass();

        // if ($class->getName() !== AbstractModule::class && !$class->isSubclassOf(AbstractModule::class)) {
        if (AbstractModule::class !== $class->getName()) {
            return;
        }

        $metadata->setDiscriminatorMap($this->modulesMappings);
    }

    public function getSubscribedEvents()
    {
        return [
            Events::loadClassMetadata,
        ];
    }
}
