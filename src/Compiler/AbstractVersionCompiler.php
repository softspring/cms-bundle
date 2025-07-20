<?php

namespace Softspring\CmsBundle\Compiler;

use Softspring\CmsBundle\Model\CompilableInterface;
use Softspring\CmsBundle\Model\CompiledDataInterface;
use Softspring\CmsBundle\Model\SiteInterface;
use Softspring\CmsBundle\Model\VersionInterface;
use Softspring\CmsBundle\Render\Error\RenderErrorException;
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

    protected function saveExceptionInCompiledData(CompiledDataInterface $compiledData, \Throwable $exception): void
    {
        // flag errors
        $compiledData->setErrors(true);

        $exceptions = [];

        $currentException = $exception;
        while ($currentException) {
            $exceptionData = [
                'class' => get_class($currentException),
                'message' => $currentException->getMessage(),
                'code' => $currentException->getCode(),
                'file' => $currentException->getFile(),
                'line' => $currentException->getLine(),
                'trace' => $currentException->getTraceAsString(),
            ];

            // store error list
            if ($currentException instanceof RenderErrorException) {
                $exceptionData['render_error_list'] = $currentException->getRenderErrorList()->getErrorsAsArray();
            }

            $exceptions[] = $exceptionData;

            $currentException = $currentException->getPrevious();
        }

        $compiledData->setDataPart('errors', $exceptions);
    }
}
