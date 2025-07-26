<?php

namespace Softspring\CmsBundle\Compiler;

use Softspring\CmsBundle\Model\CompiledDataInterface;
use Softspring\CmsBundle\Model\VersionInterface;
use Symfony\Component\HttpFoundation\Request;

interface CompilerInterface
{
    /**
     * @return CompiledDataInterface[]
     */
    public function compileAll(VersionInterface $version): array;

    public function compileRequest(VersionInterface $version, Request $request): CompiledDataInterface;
}
