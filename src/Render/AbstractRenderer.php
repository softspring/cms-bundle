<?php

namespace Softspring\CmsBundle\Render;

use Exception;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\SiteInterface;
use Softspring\CmsBundle\Render\Exception\RenderException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;

abstract class AbstractRenderer
{
    public function __construct(
        protected RequestStack $requestStack,
        protected ?EntrypointLookupInterface $entrypointLookup,
        protected RouterInterface $router,
    ) {
    }

    protected function isPreview(): bool
    {
        return $this->requestStack->getCurrentRequest()?->attributes->has('_cms_preview') ?: false;
    }

    /**
     * @throws RenderException
     */
    protected function encapsulateEsiCapableRender(callable $renderFunction)
    {
        $currentRequest = $this->requestStack->getCurrentRequest();

        if ($currentRequest->headers->has('Surrogate-Capability')) {
            $originalSurrogateCapability = $currentRequest->headers->get('Surrogate-Capability');
        }

        $currentRequest->headers->set('Surrogate-Capability', 'ESI/1.0');

        $result = $this->encapsulateRequestRender($currentRequest, $renderFunction);

        isset($originalSurrogateCapability) ? $currentRequest->headers->set('Surrogate-Capability', $originalSurrogateCapability) : $currentRequest->headers->remove('Surrogate-Capability');

        return $result;
    }

    /**
     * @throws RenderException
     */
    protected function encapsulateRequestRender(Request $request, callable $renderFunction)
    {
        $this->entrypointLookup && $this->entrypointLookup->reset();
        $this->requestStack->push($request);

        if ($request->attributes->has('_sfs_cms_site')) {
            $prevRequestContext = $this->router->getContext();
            $newRequestContext = new RequestContext();
            $site = $request->attributes->get('_sfs_cms_site');
            $newRequestContext->setHost($site->getCanonicalHost());
            $newRequestContext->setScheme($site->getCanonicalScheme());
            $newRequestContext->setBaseUrl('');
            $this->router->setContext($newRequestContext);
            $request->attributes->set('_locale', $request->getLocale());
        }

        try {
            $result = $renderFunction($request);
        } catch (Exception $e) {
            if ($e instanceof RenderException) {
                throw $e;
            }
            throw new RenderException('Error rendering request', 0, $e);
        } finally {
            isset($prevRequestContext) && $this->router->setContext($prevRequestContext);
            $this->requestStack->pop();
        }

        return $result;
    }

    public static function generateRequestForContent(ContentInterface $content, string $locale, SiteInterface $site, bool $preview = false): Request
    {
        $request = self::generateRequest($locale, $site, $preview);

        if ($routePath = $content->getCanonicalRoutePath($locale)) {
            $request->attributes->set('routePath', $routePath);
            $request->attributes->set('_route', $routePath->getRoute()->getId());
        }

        return $request;
    }

    public static function generateRequest(string $locale, SiteInterface $site, bool $preview = false): Request
    {
        $parameters = [];
        $cookies = [];
        $files = [];
        $server = [
            'SERVER_NAME' => $site->getCanonicalHost(),
            'HTTP_HOST' => $site->getCanonicalHost(),
            'HTTPS' => 'on',
            'SERVER_PORT' => 443,
            'REQUEST_URI' => '/',
            'QUERY_STRING' => '',
        ];
        $body = null;

        $request = Request::create('', 'GET', $parameters, $cookies, $files, $server, $body);
        $request->setLocale($locale);
        $request->attributes->set('_sfs_cms_site', $site);
        $request->setSession(new Session(new MockArraySessionStorage()));

        if ($preview) {
            $request->attributes->set('_cms_preview', true);
        }

        return $request;
    }
}
