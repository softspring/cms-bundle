<?php

namespace Softspring\CmsBundle\Routing;

use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Exception\SiteHasNotACanonicalHostException;
use Softspring\CmsBundle\Exception\SiteNotFoundException;
use Softspring\CmsBundle\Exception\SiteResolutionException;
use Softspring\CmsBundle\Model\SiteInterface;
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
     * @throws SiteResolutionException
     */
    public function resolveSiteAndHost(Request $request): ?array
    {
        switch ($this->siteConfig['identification']) {
            case 'domain':
                $host = $request->getHost();
                foreach ($this->cmsConfig->getSites() as $siteId => $site) {
                    foreach ($site->getConfig()['hosts'] as $hostConfig) {
                        if ($host === $hostConfig['domain']) {
                            return [$siteId, $site, $hostConfig];
                        }
                    }
                }
                break;

            case 'path':
                // $path = $request->getBasePath();
            default:
                throw new SiteResolutionException(sprintf('Site resolution %s identification method is not yet implemented', $this->siteConfig['identification']));
        }

        if ($this->siteConfig['throw_not_found']) {
            throw new SiteNotFoundException();
        }

        return [null, null, null];
    }

    /**
     * @throws SiteHasNotACanonicalHostException
     */
    public function getCanonicalRedirectUrl(SiteInterface $site, Request $request): string
    {
        $canonicalHost = '';
        $canonicalScheme = $request->getScheme();
        foreach ($site->getConfig()['hosts'] as $hostConfig) {
            if ($hostConfig['canonical']) {
                $canonicalHost = $hostConfig['domain'];
                if ($hostConfig['scheme']) {
                    $canonicalScheme = $hostConfig['scheme'];
                }
                break;
            }
        }

        if (!$canonicalHost) {
            throw new SiteHasNotACanonicalHostException();
        }

        $queryString = $request->getQueryString();

        return $canonicalScheme.'://'.$canonicalHost.$request->getPathInfo().($queryString ? '?'.$queryString : '');
    }
}
