<?php

namespace Softspring\CmsBundle\Model;

abstract class Site implements SiteInterface
{
    /**
     * @var boolean
     */
    protected $enabled = false;

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     */
    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }
}