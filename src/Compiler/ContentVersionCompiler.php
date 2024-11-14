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
use Softspring\CmsBundle\Model\SiteInterface;
use Softspring\CmsBundle\Render\ContentVersionRenderer;
use Softspring\CmsBundle\Render\Error\RenderErrorException;
use Softspring\CmsBundle\Render\Error\RenderErrorList;
use Softspring\CmsBundle\Render\Exception\RenderException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ContentVersionCompiler
{
    public function __construct(
        protected ContentVersionRenderer $contentRender,
        protected RequestStack $requestStack,
        protected array $enabledLocales,
        protected ?LoggerInterface $cmsLogger,
        protected string $prefixCompiled,
        protected bool $saveCompiled,
        protected CmsConfig $cmsConfig,
        protected CompiledDataManagerInterface $compiledDataManager,
    ) {
    }

    public function clearCompiled(ContentVersionInterface $contentVersion): void
    {
        $contentVersion->getCompiled()->map(function (CompiledDataInterface $compiled) use ($contentVersion) {
            $contentVersion->removeCompiled($compiled);
        });
        $contentVersion->setCompileErrors(false);
    }

    /**
     * @throws CompileAllException
     */
    public function compileAll(ContentVersionInterface $contentVersion, bool $failOnException = true): void
    {
        if (!$this->requestStack->getCurrentRequest()) {
            return; // not yet ready for render in fixtures, TODO improve this to allow render in fixtures
        }

        $exceptions = [];

        foreach ($contentVersion->getContent()->getSites() as $site) {
            foreach ($contentVersion->getContent()->getLocales() ?? [] as $locale) {
                $this->cmsLogger && $this->cmsLogger->debug(sprintf('Compiling "%s" content version for "%s" in "%s"', $contentVersion->getContent()->getName(), "$site", $locale));
                $request = ContentVersionRenderer::generateRequestForContent($contentVersion->getContent(), $locale, $site);

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
     * @throws CompileException
     */
    public function compileRequest(ContentVersionInterface $contentVersion, Request $request, ?array $compiledModules = null, bool $failOnException = true): CompiledDataInterface
    {
        $compiledData = $this->compiledDataManager->createEntity();
        $compiledData->setKey($this->getCompileKeyFromRequest($contentVersion, $request));
        $contentVersion->addCompiled($compiledData);

        try {
            if (empty($compiledModules)) {
                /** @var array $compiledModules */
                $compiledModules = $this->compileModulesRequest($contentVersion, $request, $failOnException);
                $this->canSaveCompiledModules($contentVersion) && $compiledData->setDataPart('modules', $compiledModules);
            }

            // create structure for errors
            $renderErrors = new RenderErrorList();

            // compile data. Take into account that this method can return content and fill errors in the RenderErrorList
            $compiledCode = $this->contentRender->render($contentVersion, $request, $renderErrors, $compiledModules);
            $this->canSaveCompiled($contentVersion) && $compiledData->setDataPart('content', $compiledCode);

            // generates an exception if there are errors
            $renderErrors->buildExceptionOnErrors();
        } catch (RenderException|RenderErrorException $exception) {
            // if set to fail on exception, throw it
            if ($failOnException) {
                throw new CompileException('Error compiling content version request', 0, $exception);
            }

            // if not content was set, set a default error content
            if ($this->canSaveCompiled($contentVersion) && empty($compiledData->getDataPart('content'))) {
                $compiledData->setDataPart('content', '<!-- CONTENT_VERSION_COMPILE_ERROR -->');
            }

            // flag errors
            $compiledData->setErrors(true);

            // store error list
            $compiledData->setDataPart('errors', $exception->getRenderErrorList()->getErrorsAsArray());
        }

        return $compiledData;
    }

    /**
     * @throws CompileException
     */
    public function compileModulesRequest(ContentVersionInterface $contentVersion, Request $request, bool $failOnException = true): array
    {
        try {
            $renderErrors = new RenderErrorList();
            $compiled = $this->contentRender->renderModules($contentVersion, $request, $renderErrors);
            $renderErrors->buildExceptionOnErrors();

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
        if (false === $this->saveCompiled) {
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

    public function getCompileKeyFromRequest(ContentVersionInterface $version, Request $request): string
    {
        return $this->getCompileKey($version, $request->attributes->get('_sfs_cms_site'), $request->getLocale());
    }

    public function getCompileKey(ContentVersionInterface $version, SiteInterface $site, string $locale): string
    {
        return "{$this->prefixCompiled}{$site}/{$locale}";
    }
}
