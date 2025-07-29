<?php

namespace Softspring\CmsBundle\Helper;

use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Config\Exception\InvalidContentException;
use Softspring\CmsBundle\Config\Exception\InvalidLayoutException;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class CompileHelper
{
    public function __construct(
        protected CmsConfig $cmsConfig,
        protected RequestStack $requestStack,
        protected bool $contentSaveCompiled,
        protected bool $contentAutoCompileOnSave,
    ) {
    }

    /**
     * @throws InvalidLayoutException
     * @throws InvalidContentException
     */
    public function contentAutoCompileOnSave(ContentVersionInterface $version): bool
    {
        if (!$this->contentAutoCompileOnSave) {
            return false;
        }

        if (!$this->requestStack->getCurrentRequest()) {
            return false; // not yet ready for render in fixtures, TODO improve this to allow render in fixtures
        }

        return $this->contentSaveCompiled($version);
    }

    /**
     * @throws InvalidLayoutException
     * @throws InvalidContentException
     */
    public function contentSaveCompiled(ContentVersionInterface $version): bool
    {
        if (!$this->contentSaveCompiledContainers($version)) {
            return false;
        }

        $layoutConfig = $this->cmsConfig->getLayout($version->getLayout());

        if (false === $layoutConfig['save_compiled']) {
            return false;
        }

        return true;
    }

    /**
     * @throws InvalidContentException
     */
    public function contentSaveCompiledContainers(ContentVersionInterface $version): bool
    {
        if (false === $this->contentSaveCompiled) {
            return false;
        }

        $contentConfig = $this->cmsConfig->getContent($version->getContent());

        if (false === $contentConfig['save_compiled']) {
            return false;
        }

        return true;
    }
}
