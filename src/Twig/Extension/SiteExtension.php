<?php

namespace Softspring\CmsBundle\Twig\Extension;

use Doctrine\Common\Collections\Collection;
use Softspring\CmsBundle\Manager\SiteManagerInterface;
use Softspring\CmsBundle\Model\SiteLanguagesInterface;
use Softspring\CmsBundle\Model\SiteSimpleCountriesInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SiteExtension extends AbstractExtension
{
    protected ?SiteManagerInterface $siteManager;

    public function __construct(?SiteManagerInterface $siteManager)
    {
        $this->siteManager = $siteManager;
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('sfs_cms_site_has_languages', [$this, 'siteHasLanguages']),
            new TwigFunction('sfs_cms_site_has_countries', [$this, 'siteHasCountries']),
            new TwigFunction('sfs_cms_get_sites', [$this, 'getSites']),
        ];
    }

    public function siteHasLanguages(): bool
    {
        if (!$this->siteManager instanceof SiteManagerInterface) {
            return false;
        }

        return $this->siteManager->getEntityClassReflection()->implementsInterface(SiteLanguagesInterface::class);
    }

    public function siteHasCountries(): bool
    {
        if (!$this->siteManager instanceof SiteManagerInterface) {
            return false;
        }

        return $this->siteManager->getEntityClassReflection()->implementsInterface(SiteSimpleCountriesInterface::class);
    }

    /**
     * @return array|object[]|Collection
     */
    public function getSites()
    {
        return $this->siteManager->getRepository()->findAll();
    }
}
