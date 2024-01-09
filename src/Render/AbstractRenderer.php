<?php

namespace Softspring\CmsBundle\Render;

use Softspring\CmsBundle\Model\SiteInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;

abstract class AbstractRenderer
{
    public function __construct(
        protected RequestStack $requestStack,
        protected ?EntrypointLookupInterface $entrypointLookup
    ) {
    }

    protected function isPreview(): bool
    {
        return $this->requestStack->getCurrentRequest()?->attributes->has('_cms_preview') ?: false;
    }

    protected function encapsulateEsiCapableRender(callable $renderFunction)
    {
        $currentRequest = $this->requestStack->getCurrentRequest();

        if ($currentRequest->headers->has('Surrogate-Capability')) {
            $originalSurrogateCapability = $currentRequest->headers->get('Surrogate-Capability');
        }

        $currentRequest->headers->set('Surrogate-Capability', 'ESI/1.0');

        $result = $renderFunction($currentRequest);

        isset($originalSurrogateCapability) ? $currentRequest->headers->set('Surrogate-Capability', $originalSurrogateCapability) : $currentRequest->headers->remove('Surrogate-Capability');

        return $result;
    }

    protected function encapsulateRequestRender(Request $request, callable $renderFunction)
    {
        $this->entrypointLookup && $this->entrypointLookup->reset();
        $this->requestStack->push($request);
        $result = $renderFunction($request);
        $this->requestStack->pop();

        return $result;
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
