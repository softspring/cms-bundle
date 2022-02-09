<?php

namespace Softspring\CmsBundle\Model;

abstract class Site implements SiteInterface
{
    protected bool $enabled = false;

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     */
    public function setEnabled(?bool $enabled): void
    {
        $this->enabled = (bool) $enabled;
    }
}
