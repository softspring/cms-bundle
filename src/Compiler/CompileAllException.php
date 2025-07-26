<?php

namespace Softspring\CmsBundle\Compiler;

use Exception;
use Throwable;

class CompileAllException extends Exception implements CompileExceptionDetailsInterface
{
    /**
     * @param Throwable[] $exceptions
     */
    public function __construct(protected array $exceptions, string $message = 'Error compiling all content version', int $code = 0)
    {
        parent::__construct($message, $code);
    }

    /**
     * @return Throwable[]
     */
    public function getExceptions(): array
    {
        return $this->exceptions;
    }

    public function getDetails(): string
    {
        $details = [];
        foreach ($this->exceptions as $exception) {
            if ($exception instanceof CompileExceptionDetailsInterface) {
                $details[] = $exception->getDetails();
            } else {
                $details[] = $exception->getMessage();
            }
        }

        return implode("\n", $details);
    }
}
