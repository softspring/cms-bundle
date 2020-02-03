<?php

namespace Softspring\CmsBundle\Twig\Extension;

use Softspring\CmsBundle\Manager\SiteManagerInterface;
use Softspring\CmsBundle\Model\SiteLanguagesInterface;
use Softspring\CmsBundle\Model\SiteSimpleCountriesInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CmsExtension extends AbstractExtension
{
    /**
     * @var SiteManagerInterface|null
     */
    protected $siteManager;

    /**
     * CmsExtension constructor.
     *
     * @param SiteManagerInterface|null $siteManager
     */
    public function __construct(?SiteManagerInterface $siteManager)
    {
        $this->siteManager = $siteManager;
    }


    /**
     * @inheritDoc
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('sfs_cms_site_has_languages', [$this, 'siteHasLanguages']),
            new TwigFunction('sfs_cms_site_has_countries', [$this, 'siteHasCountries']),
        ];
    }

    /**
     * @return bool
     */
    public function siteHasLanguages(): bool
    {
        if (!$this->siteManager instanceof SiteManagerInterface) {
            return false;
        }

        return $this->siteManager->getEntityClassReflection()->implementsInterface(SiteLanguagesInterface::class);
    }

    /**
     * @return bool
     */
    public function siteHasCountries(): bool
    {
        if (!$this->siteManager instanceof SiteManagerInterface) {
            return false;
        }

        return $this->siteManager->getEntityClassReflection()->implementsInterface(SiteSimpleCountriesInterface::class);
    }

}