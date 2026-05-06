<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Compiler;

use Softspring\CmsBundle\Model\CompiledDataInterface;
use Softspring\CmsBundle\Render\Error\RenderErrorException;
use Throwable;

abstract class AbstractVersionCompiler implements CompilerInterface
{
    protected function saveExceptionInCompiledData(CompiledDataInterface $compiledData, Throwable $exception, string $errorsKey = 'errors'): void
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

        $compiledData->setDataPart($errorsKey, $exceptions);
    }
}
