<?php

namespace Softspring\CmsBundle\Model;

interface SiteInterface
{
    /**
     * @return bool
     */
    public function isEnabled(): bool;

    /**
     * @param bool $enabled
     */
    public function setEnabled(bool $enabled): void;
}