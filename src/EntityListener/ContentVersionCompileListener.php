<?php

namespace Softspring\CmsBundle\EntityListener;

use Doctrine\ORM\Event\PrePersistEventArgs;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\Render\ContentVersionCompiler;

class ContentVersionCompileListener
{
    protected ContentVersionCompiler $contentVersionCompiler;

    public function __construct(ContentVersionCompiler $contentVersionCompiler)
    {
        $this->contentVersionCompiler = $contentVersionCompiler;
    }

    public function prePersist(ContentVersionInterface $contentVersion, PrePersistEventArgs $event): void
    {
        $this->contentVersionCompiler->compile($contentVersion);
    }
}
