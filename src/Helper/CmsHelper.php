<?php

namespace Softspring\CmsBundle\Helper;

use Softspring\CmsBundle\Config\CmsConfig;

class CmsHelper
{
    public function __construct(
        protected CmsConfig $cmsConfig,
        protected SiteHelper $siteHelper,
        protected LocaleHelper $localeHelper,
        protected LayoutHelper $layoutHelper,
    ) {
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

    public function layout(): LayoutHelper
    {
        return $this->layoutHelper;
    }
}
