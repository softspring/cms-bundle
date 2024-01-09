<?php

namespace Softspring\CmsBundle\Render;

use Psr\Log\LoggerInterface;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Config\Exception\InvalidContentException;
use Softspring\CmsBundle\Config\Exception\InvalidLayoutException;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\Model\SiteInterface;
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
    ) {
    }

    /**
     * @throws InvalidLayoutException
     * @throws CompileException
     * @throws InvalidContentException
     */
    public function compileAll(ContentVersionInterface $contentVersion): array
    {
        if (!$this->requestStack->getCurrentRequest()) {
            return []; // not yet ready for render in fixtures, TODO improve this to allow render in fixtures
        }

        $compiled = [];
        $compiledModules = [];
        foreach ($contentVersion->getContent()->getSites() as $site) {
            // TODO REFACTOR ON TRANSLATIONS MERGE
            foreach ($this->enabledLocales as $locale) {
                $this->cmsLogger && $this->cmsLogger->debug(sprintf('Compiling "%s" content version for "%s" in "%s"', $contentVersion->getContent()->getName(), "$site", $locale));

                $request = ContentVersionRenderer::generateRequest($locale, $site);

                if ($routePath = $contentVersion->getContent()->getCanonicalRoutePath($locale)) {
                    $request->attributes->set('routePath', $routePath);
                }

                $compileKey = $this->getCompileKeyFromRequest($contentVersion, $request);

                $compiledModules[$compileKey] = $this->compileModulesRequest($contentVersion, $request);
                $this->canSaveCompiledModules($contentVersion) && $contentVersion->setCompiledModules($compiledModules);

                $compiled[$compileKey] = $this->compileRequest($contentVersion, $request, $compiledModules[$compileKey]);
                $this->canSaveCompiled($contentVersion) && $contentVersion->setCompiled($compiled);
            }
        }

        return [$compiled, $compiledModules];
    }

    /**
     * @throws CompileException
     */
    public function compileRequest(ContentVersionInterface $contentVersion, Request $request, array $compiledModules = null): string
    {
        try {
            $renderErrors = new RenderErrorList();
            $compiled = $this->contentRender->render($contentVersion, $request, $renderErrors, $compiledModules);
            $renderErrors->buildExceptionOnErrors();

            return $compiled;
        } catch (\Exception $exception) {
            $this->cmsLogger && $this->cmsLogger->error(sprintf('Error compiling "%s" content version for "%s" in "%s"', $contentVersion->getContent()->getName(), $request->attributes->get('_sfs_cms_site'), $request->getLocale()), [
                'exception' => $exception,
            ]);

            throw new CompileException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    /**
     * @throws CompileException
     */
    public function compileModulesRequest(ContentVersionInterface $contentVersion, Request $request): array
    {
        try {
            $renderErrors = new RenderErrorList();
            $compiled = $this->contentRender->renderModules($contentVersion, $request, $renderErrors);
            $renderErrors->buildExceptionOnErrors();

            return $compiled;
        } catch (\Exception $exception) {
            $this->cmsLogger && $this->cmsLogger->error(sprintf('Error compiling "%s" content version for "%s" in "%s"', $contentVersion->getContent()->getName(), $request->attributes->get('_sfs_cms_site'), $request->getLocale()), [
                'exception' => $exception,
            ]);

            throw new CompileException($exception->getMessage(), $exception->getCode(), $exception);
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
