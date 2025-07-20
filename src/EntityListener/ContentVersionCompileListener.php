<?php

namespace Softspring\CmsBundle\EntityListener;

use Doctrine\ORM\Event\PrePersistEventArgs;
use Softspring\CmsBundle\Compiler\CompileAllException;
use Softspring\CmsBundle\Compiler\ContentVersionCompiler;
use Softspring\CmsBundle\Model\ContentVersionInterface;

class ContentVersionCompileListener
{
    public function __construct(
        protected ContentVersionCompiler $contentVersionCompiler,
        protected bool $contentSaveCompiled,
        protected bool $contentAutoCompileOnSave,
    ) {
    }

    /**
     * @throws CompileAllException
     */
    public function prePersist(ContentVersionInterface $contentVersion, PrePersistEventArgs $event): void
    {
        if (!$this->contentAutoCompileOnSave || !$this->contentSaveCompiled) {
            return;
        }

        $this->contentVersionCompiler->compileAll($contentVersion, true);
    }
}
