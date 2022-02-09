<?php

namespace Softspring\CmsBundle\Model;

interface SiteInterface
{
    public function isEnabled(): bool;

    public function setEnabled(bool $enabled): void;
}
