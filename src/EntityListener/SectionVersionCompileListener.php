<?php

namespace Softspring\CmsBundle\EntityListener;

use Doctrine\ORM\Event\PrePersistEventArgs;
use Softspring\CmsBundle\Compiler\SectionVersionCompiler;
use Softspring\CmsBundle\Helper\CompileHelper;
use Softspring\CmsBundle\Model\SectionVersionInterface;

class SectionVersionCompileListener
{
    public function __construct(
        protected CompileHelper $compileHelper,
        protected SectionVersionCompiler $sectionVersionCompiler,
    ) {
    }

    public function prePersist(SectionVersionInterface $sectionVersion, PrePersistEventArgs $event): void
    {
        if (!$this->compileHelper->sectionAutoCompileOnSave($sectionVersion)) {
            return;
        }

        foreach ($this->sectionVersionCompiler->compileAll($sectionVersion) as $compiledData) {
            $sectionVersion->addCompiled($compiledData);
        }
    }
}
