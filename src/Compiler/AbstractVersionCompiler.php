<?php

namespace Softspring\CmsBundle\Compiler;

use Softspring\CmsBundle\Model\CompilableInterface;
use Softspring\CmsBundle\Model\CompiledDataInterface;
use Softspring\CmsBundle\Model\SiteInterface;
use Softspring\CmsBundle\Model\VersionInterface;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractVersionCompiler
{
    protected string $prefixCompiled;

    public function clearCompiled(VersionInterface|CompilableInterface $version): void
    {
        $version->getCompiled()->map(function (CompiledDataInterface $compiled) use ($version) {
            $version->removeCompiled($compiled);
        });
        $version->setCompileErrors(false);
    }

    public function getCompileKeyFromRequest(VersionInterface $version, Request $request): string
    {
        return $this->getCompileKey($version, $request->getLocale(), $request->attributes->get('_sfs_cms_site'));
    }

    public function getCompileKey(VersionInterface $version, string $locale, ?SiteInterface $site = null): string
    {
        return "{$this->prefixCompiled}{$site}/{$locale}";
    }
}
