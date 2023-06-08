<?php

namespace Softspring\CmsBundle\Render;

use Psr\Log\LoggerInterface;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\Model\RouteInterface;
use Softspring\CmsBundle\Model\SiteInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ContentVersionCompiler
{
    protected ContentRender $contentRender;
    protected RequestStack $requestStack;
    protected UrlGeneratorInterface $urlGenerator;
    protected array $enabledLocales;
    protected ?LoggerInterface $cmsLogger;

    public function __construct(ContentRender $contentRender, RequestStack $requestStack, UrlGeneratorInterface $urlGenerator, array $enabledLocales, ?LoggerInterface $cmsLogger)
    {
        $this->contentRender = $contentRender;
        $this->enabledLocales = $enabledLocales;
        $this->urlGenerator = $urlGenerator;
        $this->requestStack = $requestStack;
        $this->cmsLogger = $cmsLogger;
    }

    public function generateRequest(string $locale, SiteInterface $site, ContentVersionInterface $contentVersion): Request
    {
        /** @var ?RouteInterface $route */
        $route = $contentVersion->getContent()->getRoutes()->first();
        $contentUrl = $route ? $this->urlGenerator->generate($route->getId(), [], UrlGeneratorInterface::ABSOLUTE_URL) : '';

        $parameters = [];
        $cookies = [];
        $files = [];
        $server = [];
        $body = null;

        $request = Request::create($contentUrl, 'GET', $parameters, $cookies, $files, $server, $body);
        $request->setLocale($locale);
        $request->attributes->set('_sfs_cms_site', $site);
        $request->setSession(new Session(new MockArraySessionStorage()));

        return $request;
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

                $this->requestStack->push($this->generateRequest($locale, $site, $contentVersion));
                $renderErrors = new RenderErrorList();
                $compiledModules["$site"][$locale] = $this->contentRender->renderModules($contentVersion, $renderErrors);
                $this->requestStack->pop();
                $renderErrors->buildExceptionOnErrors();
            }
        }
        $contentVersion->setCompiled($compiled);
        $contentVersion->setCompiledModules($compiledModules);
    }
}
