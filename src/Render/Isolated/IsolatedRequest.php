<?php

namespace Softspring\CmsBundle\Render\Isolated;

use BadMethodCallException;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\RoutePathInterface;
use Softspring\CmsBundle\Model\SiteInterface;
use Softspring\CmsBundle\Render\Exception\IsolatedEnvironmentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * IsolatedRequest is a wrapper around Request that prevents access to user, token, and session methods.
 * This is useful in CMS renders where such access is not allowed.
 */
class IsolatedRequest extends Request
{
    public static function createIsolated(string $locale, SiteInterface $site, bool $preview = false): IsolatedRequest
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

        $request = Request::create('/', 'GET', $parameters, $cookies, $files, $server, $body);
        $request->setLocale($locale);
        $request->attributes->set('_sfs_cms_site', $site);
        // $request->setSession(new Session(new MockArraySessionStorage()));

        if ($preview) {
            $request->attributes->set('_cms_preview', true);
        }

        $isolatedRequest = new self($request);
        $isolatedRequest->attributes = $isolatedRequest->inner->attributes;
        $isolatedRequest->request = $isolatedRequest->inner->request;
        $isolatedRequest->query = $isolatedRequest->inner->query;
        $isolatedRequest->server = $isolatedRequest->inner->server;
        $isolatedRequest->files = $isolatedRequest->inner->files;
        $isolatedRequest->cookies = $isolatedRequest->inner->cookies;
        $isolatedRequest->headers = $isolatedRequest->inner->headers;

        return $isolatedRequest;
    }

    public static function createIsolatedForContentRoute(ContentInterface $content, string $locale, SiteInterface $site, bool $preview = false): IsolatedRequest
    {
        $isolatedRequest = IsolatedRequest::createIsolated($locale, $site, $preview);

        if (($routePath = $content->getCanonicalRoutePath($locale)) instanceof RoutePathInterface) {
            $isolatedRequest->attributes->set('routePath', $routePath);
            $isolatedRequest->attributes->set('_route', $routePath->getRoute()->getId());
        }

        return $isolatedRequest;
    }

    public function __construct(protected Request $inner)
    {
        parent::__construct();
    }

    public function __call(string $method, array $params)
    {
        if (method_exists($this->inner, $method)) {
            return call_user_func_array([$this->inner, $method], $params);
        }

        throw new BadMethodCallException(sprintf('Method "%s" does not exist in IsolatedRequest.', $method));
    }

    public function __get(string $name): mixed
    {
        if (property_exists($this->inner, $name)) {
            return $this->inner->{$name};
        }

        throw new BadMethodCallException(sprintf('Property "%s" does not exist in IsolatedRequest.', $name));
    }

    /**
     * @throws IsolatedEnvironmentException
     */
    public function getSession(): SessionInterface
    {
        throw new IsolatedEnvironmentException(IsolatedRequest::class, 'getSession');
    }

    /**
     * @throws IsolatedEnvironmentException
     */
    public function setSession(SessionInterface $session): void
    {
        throw new IsolatedEnvironmentException(IsolatedRequest::class, 'setSession');
    }

    public function getLocale(): string
    {
        return $this->inner->getLocale();
    }

    public function setLocale(string $locale): void
    {
        $this->inner->setLocale($locale);
    }

    public function getScheme(): string
    {
        return $this->inner->getScheme();
    }

    public function getHost(): string
    {
        return $this->inner->getHost();
    }
}
