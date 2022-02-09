<?php

namespace Softspring\CmsBundle\Render\Module;

use Softspring\CmsBundle\Entity\AbstractModule;

interface ModuleRenderInterface
{
    public function render(AbstractModule $module): string;

    public function supports(AbstractModule $module): bool;
}
