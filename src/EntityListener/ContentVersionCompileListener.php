<?php

namespace Softspring\CmsBundle\EntityListener;

use Doctrine\ORM\Event\PrePersistEventArgs;
use Softspring\CmsBundle\Config\Exception\InvalidContentException;
use Softspring\CmsBundle\Config\Exception\InvalidLayoutException;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\Render\CompileException;
use Softspring\CmsBundle\Render\ContentVersionCompiler;

class ContentVersionCompileListener
{
    protected ContentVersionCompiler $contentVersionCompiler;

    public function __construct(ContentVersionCompiler $contentVersionCompiler)
    {
        $this->contentVersionCompiler = $contentVersionCompiler;
    }

    /**
     * @throws InvalidLayoutException
     * @throws CompileException
     * @throws InvalidContentException
     */
    public function prePersist(ContentVersionInterface $contentVersion, PrePersistEventArgs $event): void
    {
        $this->contentVersionCompiler->compileAll($contentVersion);
    }
}
