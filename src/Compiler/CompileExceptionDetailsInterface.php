<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Compiler;

interface CompileExceptionDetailsInterface
{
    public function getDetails(): string;
}
