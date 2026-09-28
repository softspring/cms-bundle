<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Helper;

use Softspring\CmsBundle\Config\CmsConfig;

class CmsHelper
{
    public function __construct(
        protected CmsConfig $cmsConfig,
        protected SiteHelper $siteHelper,
        protected LocaleHelper $localeHelper,
        protected LayoutHelper $layoutHelper,
        protected CompileHelper $compileHelper,
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

    public function compile(): CompileHelper
    {
        return $this->compileHelper;
    }
}
