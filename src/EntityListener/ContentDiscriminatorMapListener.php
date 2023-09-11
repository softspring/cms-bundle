<?php

namespace Softspring\CmsBundle\EntityListener;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Mapping\MappingException;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Entity\Content;

class ContentDiscriminatorMapListener
{
    protected CmsConfig $cmsConfig;

    public function __construct(CmsConfig $cmsConfig)
    {
        $this->cmsConfig = $cmsConfig;
    }

    /**
     * @throws MappingException
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        /** @var ClassMetadataInfo $metadata */
        $metadata = $eventArgs->getClassMetadata();
        $class = $metadata->getReflectionClass();

        if (Content::class !== $class->getName()) {
            return;
        }

        foreach ($this->cmsConfig->getContentMappings() as $discriminator => $class) {
            $metadata->addDiscriminatorMapClass($discriminator, $class);
        }
    }
}
