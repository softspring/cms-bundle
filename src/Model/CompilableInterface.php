<?php

namespace Softspring\CmsBundle\Model;

use Doctrine\Common\Collections\Collection;

interface CompilableInterface
{
    /**
     * @return Collection<CompiledDataInterface>
     */
    public function getCompiled(): Collection;

    public function setCompiled(Collection $compiled): void;

    public function addCompiled(CompiledDataInterface $compiled): void;

    public function removeCompiled(CompiledDataInterface $compiled): void;

    public function cleanCompiled(): void;

    public function hasCompileErrors(): bool;

    public function setCompileErrors(bool $compileErrors): void;
}
