<?php

namespace Softspring\CmsBundle\Compiler;

use Exception;
use Psr\Log\LoggerInterface;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Config\Exception\InvalidContentException;
use Softspring\CmsBundle\Config\Exception\InvalidLayoutException;
use Softspring\CmsBundle\Manager\CompiledDataManagerInterface;
use Softspring\CmsBundle\Model\CompiledDataInterface;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\Render\ContentVersionRenderer;
use Softspring\CmsBundle\Render\Error\RenderErrorException;
use Softspring\CmsBundle\Render\Error\RenderErrorList;
use Softspring\CmsBundle\Render\Exception\RenderException;
use Softspring\CmsBundle\Render\Isolated\IsolatedRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ContentVersionCompiler extends AbstractVersionCompiler
{
    public function __construct(
        protected ContentVersionRenderer $contentVersionRender,
        protected RequestStack $requestStack,
        protected array $enabledLocales,
        protected bool $contentSaveCompiled,
        protected CmsConfig $cmsConfig,
        protected CompiledDataManagerInterface $compiledDataManager,
        string $prefixCompiled,
        protected ?LoggerInterface $cmsLogger,
    ) {
        $this->prefixCompiled = $prefixCompiled;
    }

    /**
     * @throws CompileAllException
     * @throws InvalidLayoutException
     * @throws InvalidContentException
     */
    public function compileAll(ContentVersionInterface $contentVersion, bool $failOnException = true): void
    {
        if (!$this->requestStack->getCurrentRequest()) {
            return; // not yet ready for render in fixtures, TODO improve this to allow render in fixtures
        }

        if (!$this->contentSaveCompiled) {
            return;
        }

        $exceptions = [];

        foreach ($contentVersion->getContent()->getSites() as $site) {
            foreach ($contentVersion->getContent()->getLocales() ?? [] as $locale) {
                $this->cmsLogger && $this->cmsLogger->debug(sprintf('Compiling "%s" content version for "%s" in "%s"', $contentVersion->getContent()->getName(), "$site", $locale));
                $request = IsolatedRequest::createIsolatedForContentRoute($contentVersion->getContent(), $locale, $site);

                try {
                    $this->compileRequest($contentVersion, $request, null, $failOnException);
                } catch (CompileException $exception) {
                    $exceptions[] = $exception;
                }
            }
        }

        if (!empty($exceptions) && $failOnException) {
            throw new CompileAllException($exceptions);
        }
    }

    /**
     * @throws InvalidLayoutException
     * @throws InvalidContentException
     * @throws CompileException
     */
    public function compileRequest(ContentVersionInterface $contentVersion, Request $request, ?array $compiledContainers = null, bool $failOnException = true): CompiledDataInterface
    {
        $compiledData = $this->compiledDataManager->createEntity();
        $compiledData->setKey($this->getCompileKeyFromRequest($contentVersion, $request));

        $this->canSaveCompiled($contentVersion) && $contentVersion->addCompiled($compiledData);

        $renderErrors = new RenderErrorList();

        if (empty($compiledContainers)) {
            try {
                $compiledContainers = $this->compileContainersRequest($contentVersion, $request, $renderErrors, false);
                $this->canSaveCompiledModules($contentVersion) && $compiledData->setDataPart('containers', $compiledContainers);

                if ($renderErrors->hasErrors()) {
                    $compiledData->setErrors(true);
                    $compiledData->setDataPart('containers_errors', $renderErrors->getErrorsAsArray());
                }
            } catch (CompileException $exception) {
                if ($failOnException) {
                    throw new CompileException('Error compiling content version request', 0, $exception);
                }

                $this->canSaveCompiledModules($contentVersion) && $compiledData->setDataPart('containers', $compiledContainers);

                $this->saveExceptionInCompiledData($compiledData, $exception);

                return $compiledData;
            }
        }

        try {
            // compile data. Take into account that this method can return content and fill errors in the RenderErrorList
            $compiledCode = $this->contentVersionRender->render($contentVersion, $request, $renderErrors, $compiledContainers);
            $compiledData->setDataPart('content', $compiledCode);

            // generates an exception if there are errors
            $failOnException && $renderErrors->buildExceptionOnErrors();

            if ($renderErrors->hasErrors()) {
                $compiledData->setErrors(true);
                $compiledData->setDataPart('errors', $renderErrors->getErrorsAsArray());
            }
        } catch (RenderException|RenderErrorException|CompileException $exception) {
            // if not content was set, set a default error content
            if ($this->canSaveCompiled($contentVersion) && empty($compiledData->getDataPart('content'))) {
                $compiledData->setDataPart('content', '<!-- CONTENT_VERSION_COMPILE_ERROR -->');
            }

            $this->saveExceptionInCompiledData($compiledData, $exception);

            // if set to fail on exception, throw it
            if ($failOnException) {
                throw new CompileException('Error compiling content version request', 0, $exception);
            }
        }

        return $compiledData;
    }

    /**
     * @throws CompileException
     */
    public function compileContainersRequest(ContentVersionInterface $contentVersion, Request $request, RenderErrorList $renderErrors, bool $failOnException = true): array
    {
        try {
            $compiled = $this->contentVersionRender->renderContainers($contentVersion, $request, $renderErrors);
            $failOnException && $renderErrors->buildExceptionOnErrors();

            return $compiled;
        } catch (Exception $exception) {
            $this->cmsLogger && $this->cmsLogger->error(sprintf('Error compiling "%s" content version for "%s" in "%s"', $contentVersion->getContent()->getName(), $request->attributes->get('_sfs_cms_site'), $request->getLocale()), [
                'exception' => $exception,
            ]);

            //            if (!$failOnException) {
            //                return [];
            //            }

            throw new CompileException('Error compiling content version modules', 0, $exception);
        }
    }

    /**
     * @throws InvalidContentException
     */
    public function canSaveCompiledModules(ContentVersionInterface $version): bool
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

    /**
     * @throws InvalidLayoutException
     * @throws InvalidContentException
     */
    public function canSaveCompiled(ContentVersionInterface $version): bool
    {
        if (!$this->canSaveCompiledModules($version)) {
            return false;
        }

        $layoutConfig = $this->cmsConfig->getLayout($version->getLayout());

        if (false === $layoutConfig['save_compiled']) {
            return false;
        }

        return true;
    }
}
