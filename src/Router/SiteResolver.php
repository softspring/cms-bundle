<?php

namespace Softspring\CmsBundle\Router;

use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Exception\SiteHasNotACanonicalHostException;
use Softspring\CmsBundle\Exception\SiteNotFoundException;
use Symfony\Component\HttpFoundation\Request;

class SiteResolver
{
    protected CmsConfig $cmsConfig;
    protected array $siteConfig;

    public function __construct(CmsConfig $cmsConfig, array $siteConfig)
    {
        $this->cmsConfig = $cmsConfig;
        $this->siteConfig = $siteConfig;
    }

    /**
     * @throws SiteNotFoundException
     */
    public function getSiteAndHost(Request $request): ?array
    {
        switch ($this->siteConfig['identification']) {
            case 'domain':
                $host = $request->getHost();
                foreach ($this->cmsConfig->getSites() as $siteId => $siteConfig) {
                    foreach ($siteConfig['hosts'] as $hostConfig) {
                        if ($host === $hostConfig['domain']) {
                            return [$siteId, $siteConfig, $hostConfig];
                        }
                    }
                }
                break;

            case 'path':
                // $path = $request->getBasePath();
                throw new \Exception('Not yet implemented');
        }

        if ($this->siteConfig['throw_not_found']) {
            throw new SiteNotFoundException();
        }

        return [null, null, null];
    }

    /**
     * @throws SiteHasNotACanonicalHostException
     */
    public function getCanonicalRedirectUrl(array $siteConfig, Request $request): string
    {
        $canonicalHost = '';
        foreach ($siteConfig['hosts'] as $hostConfig) {
            if ($hostConfig['canonical']) {
                $canonicalHost = $hostConfig['domain'];
                break;
            }
        }

        if (!$canonicalHost) {
            throw new SiteHasNotACanonicalHostException();
        }

        $queryString = $request->getQueryString();
        return $request->getScheme().'://'.$canonicalHost.$request->getPathInfo().($queryString?'?'.$queryString:'');
    }
}