<?php

namespace Softspring\CmsBundle\EventListener;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

class OverrideDoctrineClassSuperclassListener
{
    protected ?array $superclassList = null;

    public function __construct(?array $superclassList)
    {
        $this->superclassList = $superclassList;
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        if (!$this->superclassList) {
            return;
        }

        /** @var ClassMetadataInfo $metadata */
        $metadata = $eventArgs->getClassMetadata();
        $class = $metadata->getReflectionClass();

        foreach ($this->superclassList as $superclassList) {
            if ($class->getName() === $superclassList) {
                $metadata->isMappedSuperclass = true;
            }
        }
    }
}
