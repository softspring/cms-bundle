<?php

namespace Softspring\CmsBundle\Compiler;

use Psr\Log\LoggerInterface;
use Softspring\CmsBundle\Helper\CmsHelper;
use Softspring\CmsBundle\Manager\CompiledDataManagerInterface;
use Softspring\CmsBundle\Model\CompiledDataInterface;
use Softspring\CmsBundle\Model\SectionVersionInterface;
use Softspring\CmsBundle\Render\Error\RenderErrorException;
use Softspring\CmsBundle\Render\Error\RenderErrorList;
use Softspring\CmsBundle\Render\Exception\RenderException;
use Softspring\CmsBundle\Render\Isolated\IsolatedRequest;
use Softspring\CmsBundle\Render\SectionVersionRenderer;
use Symfony\Component\HttpFoundation\Request;

class SectionVersionCompiler extends AbstractVersionCompiler
{
    public function __construct(
        protected SectionVersionRenderer $sectionVersionRenderer,
        protected bool $sectionSaveCompiled,
        protected CompiledDataManagerInterface $compiledDataManager,
        protected CmsHelper $cmsHelper,
        string $prefixCompiled,
        protected ?LoggerInterface $cmsLogger,
    ) {
        $this->prefixCompiled = $prefixCompiled;
    }

    /**
     * @throws CompileAllException
     */
    public function compileAll(SectionVersionInterface $version, bool $failOnException = true): void
    {
        if (!$this->sectionSaveCompiled) {
            return;
        }

        $exceptions = [];

        foreach ($this->cmsHelper->config()->getSites() as $site) {
            foreach ($this->cmsHelper->locale()->getEnabledLocales() as $locale) {
                $this->cmsLogger && $this->cmsLogger->debug(sprintf('Compiling "%s" section version in "%s"', $version->getSection()->getName(), $locale));
                $request = IsolatedRequest::createIsolated($locale, $site);

                try {
                    $this->compileRequest($version, $request, $failOnException);
                } catch (CompileException $exception) {
                    $exceptions[] = $exception;
                }
            }
        }

        if (!empty($exceptions) && $failOnException) {
            throw new CompileAllException($exceptions);
        }
    }

    public function canSaveCompiled(SectionVersionInterface $version): bool
    {
        return $this->sectionSaveCompiled;
    }

    /**
     * @throws CompileException
     */
    public function compileRequest(SectionVersionInterface $sectionVersion, Request $request, bool $failOnException = true): CompiledDataInterface
    {
        $compiledData = $this->compiledDataManager->createEntity();
        $compiledData->setKey($this->getCompileKeyFromRequest($sectionVersion, $request));

        try {
            // $this->canSaveCompiled($sectionVersion) &&
            $sectionVersion->addCompiled($compiledData);

            // create structure for errors
            $renderErrors = new RenderErrorList();

            // compile data. Take into account that this method can return content and fill errors in the RenderErrorList
            $compiledCode = $this->sectionVersionRenderer->render($sectionVersion, $request, $renderErrors);
            $compiledData->setDataPart('content', $compiledCode);

            // generates an exception if there are errors
            $renderErrors->buildExceptionOnErrors();
        } catch (RenderException|RenderErrorException $exception) {
            // if set to fail on exception, throw it
            if ($failOnException) {
                throw new CompileException('Error compiling content version request', 0, $exception);
            }

            // if not content was set, set a default error content
            if ($this->sectionSaveCompiled && empty($compiledData->getDataPart('content'))) {
                $compiledData->setDataPart('content', '<!-- CONTENT_VERSION_COMPILE_ERROR -->');
            }

            // flag errors
            $compiledData->setErrors(true);

            // store error list
            if ($exception instanceof RenderErrorException) {
                $compiledData->setDataPart('errors', $exception->getRenderErrorList()->getErrorsAsArray());
            }
        }

        return $compiledData;
    }
}
