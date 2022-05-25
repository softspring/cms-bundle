<?php

namespace Softspring\CmsBundle\Model;

interface SiteSelectorPathInterface
{
    public function setPath(?string $path): void;

    public function getPath(): ?string;
}
