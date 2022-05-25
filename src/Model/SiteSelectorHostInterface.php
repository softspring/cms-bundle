<?php

namespace Softspring\CmsBundle\Model;

interface SiteSelectorHostInterface
{
    public function setHost(?string $host): void;

    public function getHost(): ?string;
}
