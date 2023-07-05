<?php

namespace Softspring\CmsBundle\Helper;

use Softspring\CmsBundle\Config\CmsConfig;

class CmsHelper
{
    protected CmsConfig $cmsConfig;
    protected SiteHelper $siteHelper;
    protected LocaleHelper $localeHelper;

    public function __construct(CmsConfig $cmsConfig, SiteHelper $siteHelper, LocaleHelper $localeHelper)
    {
        $this->cmsConfig = $cmsConfig;
        $this->siteHelper = $siteHelper;
        $this->localeHelper = $localeHelper;
    }

    public function config(): CmsConfig
    {
        return $this->cmsConfig;
    }

    public function site(): SiteHelper
    {
        return $this->siteHelper;
    }

    public function locale(): LocaleHelper
    {
        return $this->localeHelper;
    }
}
