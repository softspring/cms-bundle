<?php

namespace Softspring\CmsBundle\EntityListener;

use Doctrine\ORM\Event\PrePersistEventArgs;
use Softspring\CmsBundle\Compiler\CompileAllException;
use Softspring\CmsBundle\Compiler\SectionVersionCompiler;
use Softspring\CmsBundle\Model\SectionVersionInterface;

class SectionVersionCompileListener
{
    public function __construct(
        protected SectionVersionCompiler $sectionVersionCompiler,
        protected bool $saveCompiled,
        protected bool $autoCompileOnSave,
    ) {
    }

    /**
     * @throws CompileAllException
     */
    public function prePersist(SectionVersionInterface $contentVersion, PrePersistEventArgs $event): void
    {
        //        if (!$this->autoCompileOnSave || !$this->saveCompiled) {
        //            return;
        //        }

        $this->sectionVersionCompiler->compileAll($contentVersion, true);
    }
}
