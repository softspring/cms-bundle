<?php

namespace Softspring\CmsBundle\EntityListener;

use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\Model\RouteInterface;
use Softspring\CmsBundle\Model\SiteInterface;
use Softspring\CmsBundle\Render\ContentRender;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ContentVersionCompileListener
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

    public function prePersist(ContentVersionInterface $contentVersion, PrePersistEventArgs $event): void
    {
        $this->saveCompiled($contentVersion, $event);
    }

    protected function generateRequest(string $locale, SiteInterface $site, ContentVersionInterface $contentVersion): Request
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

    protected function saveCompiled(ContentVersionInterface $contentVersion, LifecycleEventArgs $event): void
    {
        //        $request = $this->requestStack->getCurrentRequest();
        //
        //        if (!$request) {
        //            // if no request provided, probably is running a command, fixtures, etc.
        //            return;
        //        }
        //
        //        $originalLocale = $request->getLocale();
        //        $originalSite = $request->attributes->get('_sfs_cms_site');

        $compiled = [];
        $compiledModules = [];
        foreach ($contentVersion->getContent()->getSites() as $site) {
            foreach ($this->enabledLocales as $locale) {
                $this->requestStack->push($this->generateRequest($locale, $site, $contentVersion));
                //                $request->setLocale($locale);
                //                $request->attributes->set('_sfs_cms_site', $site);
                // $compiled[$locale] = $this->contentRender->render($contentVersion);
                $compiledModules["$site"][$locale] = $this->contentRender->renderModules($contentVersion);
                $this->requestStack->pop();
            }
        }
        $contentVersion->setCompiled($compiled);
        $contentVersion->setCompiledModules($compiledModules);

        //        $request->setLocale($originalLocale);
        //        $request->attributes->set('_sfs_cms_site', $originalSite);
    }
}
