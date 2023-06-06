<?php

namespace Softspring\CmsBundle\Render;

use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\Model\RouteInterface;
use Softspring\CmsBundle\Model\SiteInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ContentVersionCompiler
{
    protected ContentRender $contentRender;
    protected RequestStack $requestStack;
    protected UrlGeneratorInterface $urlGenerator;
    protected array $enabledLocales;

    public function __construct(ContentRender $contentRender, RequestStack $requestStack, UrlGeneratorInterface $urlGenerator, array $enabledLocales)
    {
        $this->contentRender = $contentRender;
        $this->enabledLocales = $enabledLocales;
        $this->urlGenerator = $urlGenerator;
        $this->requestStack = $requestStack;
    }

    public function generateRequest(string $locale, SiteInterface $site, ContentVersionInterface $contentVersion): Request
    {
        /** @var RouteInterface $route */
        $route = $contentVersion->getContent()->getRoutes()->first();
        $contentUrl = $this->urlGenerator->generate($route->getId(), [], UrlGeneratorInterface::ABSOLUTE_URL);

        $parameters = [];
        $cookies = [];
        $files = [];
        $server = [];
        $body = null;

        $request = Request::create($contentUrl, 'GET', $parameters, $cookies, $files, $server, $body);
        $request->setLocale($locale);
        $request->attributes->set('_sfs_cms_site', $site);
        $request->setSession(new Session());

        return $request;
    }

    public function compile(ContentVersionInterface $contentVersion): void
    {
        $compiled = [];
        $compiledModules = [];
        foreach ($contentVersion->getContent()->getSites() as $site) {
            foreach ($this->enabledLocales as $locale) {
                $this->requestStack->push($this->generateRequest($locale, $site, $contentVersion));
                $compiledModules["$site"][$locale] = $this->contentRender->renderModules($contentVersion);
                $this->requestStack->pop();
            }
        }
        $contentVersion->setCompiled($compiled);
        $contentVersion->setCompiledModules($compiledModules);
    }
}
