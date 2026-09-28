<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Compiler;

use Exception;
use Softspring\CmsBundle\Render\Error\RenderErrorException;

class CompileException extends Exception implements CompileExceptionDetailsInterface
{
    public function getDetails(): string
    {
        $details = $this->stringifyException($this);

        $previous = $this->getPrevious();
        while ($previous instanceof Exception) {
            $details .= '> '.$this->stringifyException($previous);
            $previous = $previous->getPrevious();
        }

        return '<div style="margin-bottom: 20px;">'.$details.'</div>';
    }

    private function stringifyException(Exception $exception): string
    {
        $message = $exception->getMessage();
        if ('' === $message || '0' === $message) {
            if ($exception instanceof RenderErrorException) {
                $message = 'Render error';
            } elseif ($exception instanceof CompileAllException) {
                $message = 'Compile all error';
            } else {
                $message = 'No message provided';
            }
        }

        return sprintf(
            '<span title="%s
%s:%u

%s">%s</span><br>',
            get_class($exception),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString(),
            $message
        );
    }
}
