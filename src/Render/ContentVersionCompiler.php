<?php

namespace Softspring\CmsBundle\Render;

use Psr\Log\LoggerInterface;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class ContentVersionCompiler
{
    protected ContentRender $contentRender;
    protected RequestStack $requestStack;
    protected array $enabledLocales;
    protected ?LoggerInterface $cmsLogger;

    public function __construct(ContentRender $contentRender, RequestStack $requestStack, array $enabledLocales, ?LoggerInterface $cmsLogger)
    {
        $this->contentRender = $contentRender;
        $this->enabledLocales = $enabledLocales;
        $this->requestStack = $requestStack;
        $this->cmsLogger = $cmsLogger;
    }

    /**
     * @throws RenderErrorException
     */
    public function compile(ContentVersionInterface $contentVersion): void
    {
        if (!$this->requestStack->getCurrentRequest()) {
            return; // not yet ready for render in fixtures, TODO improve this to allow render in fixtures
        }

        $compiled = [];
        $compiledModules = [];
        foreach ($contentVersion->getContent()->getSites() as $site) {
            foreach ($this->enabledLocales as $locale) {
                $this->cmsLogger && $this->cmsLogger->debug(sprintf('Compiling "%s" content version for "%s" in "%s"', $contentVersion->getContent()->getName(), "$site", $locale));

                $renderErrors = new RenderErrorList();
                $compiledModules["$site"][$locale] = $this->contentRender->renderModules($contentVersion, $site, $locale, $renderErrors);
                $renderErrors->buildExceptionOnErrors();
            }
        }
        $contentVersion->setCompiled($compiled);
        $contentVersion->setCompiledModules($compiledModules);
    }
}
