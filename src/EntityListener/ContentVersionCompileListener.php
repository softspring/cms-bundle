<?php

namespace Softspring\CmsBundle\EntityListener;

use Doctrine\ORM\Event\PrePersistEventArgs;
use Softspring\CmsBundle\Compiler\CompileException;
use Softspring\CmsBundle\Compiler\ContentVersionCompiler;
use Softspring\CmsBundle\Config\Exception\InvalidContentException;
use Softspring\CmsBundle\Config\Exception\InvalidLayoutException;
use Softspring\CmsBundle\Helper\CompileHelper;
use Softspring\CmsBundle\Model\ContentVersionInterface;

class ContentVersionCompileListener
{
    public function __construct(
        protected CompileHelper $compileHelper,
        protected ContentVersionCompiler $contentVersionCompiler,
    ) {
    }

    /**
     * @throws CompileException
     * @throws InvalidContentException
     * @throws InvalidLayoutException
     */
    public function prePersist(ContentVersionInterface $contentVersion, PrePersistEventArgs $event): void
    {
        if (!$this->compileHelper->contentAutoCompileOnSave($contentVersion)) {
            return;
        }

        foreach ($this->contentVersionCompiler->compileAll($contentVersion) as $compiledData) {
            $contentVersion->addCompiled($compiledData);
        }
    }
}
