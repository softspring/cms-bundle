<?php

namespace Softspring\CmsBundle\Render;

use Softspring\CmsBundle\Model\SiteInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

abstract class AbstractRenderer
{
    protected RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    protected function isPreview(): bool
    {
        $request = $this->requestStack->getCurrentRequest();

        return $request->attributes->has('_cms_preview');
    }

    protected function encapsulateEsiCapableRender(callable $renderFunction)
    {
        $currentRequest = $this->requestStack->getCurrentRequest();

        if ($currentRequest->headers->has('Surrogate-Capability')) {
            $originalSurrogateCapability = $currentRequest->headers->get('Surrogate-Capability');
        }

        $currentRequest->headers->set('Surrogate-Capability', 'ESI/1.0');

        $result = $renderFunction();

        isset($originalSurrogateCapability) ? $currentRequest->headers->set('Surrogate-Capability', $originalSurrogateCapability) : $currentRequest->headers->remove('Surrogate-Capability');

        return $result;
    }

    protected function encapsulateRequestRender(SiteInterface $site, string $locale, callable $renderFunction)
    {
        $this->requestStack->push($this->generateRequest($locale, $site));
        $result = $renderFunction();
        $this->requestStack->pop();

        return $result;
    }

    protected function generateRequest(string $locale, SiteInterface $site): Request
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

        return $request;
    }
}
