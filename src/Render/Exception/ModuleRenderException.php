<?php

namespace Softspring\CmsBundle\Render\Exception;

use Exception;
use Throwable;

class ModuleRenderException extends Exception
{
    public function __construct(protected array $module, ?Throwable $previous = null)
    {
        parent::__construct(sprintf('Error rendering module %s', $this->getModuleName()), 0, $previous);
    }

    public function getModuleName(): string
    {
        return $this->module['_module'] ?? 'unknown module';
    }

    public function getModule(): array
    {
        return $this->module;
    }
}
