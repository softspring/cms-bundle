<?php

namespace Softspring\CmsBundle\Model\Traits;

use DateTime;
use Doctrine\Common\Collections\Collection;
use Softspring\CmsBundle\Model\CompiledDataInterface;

trait CompilableTrait
{
    protected ?Collection $compiled = null;

    protected bool $compileErrors = false;

    /**
     * @return Collection<CompiledDataInterface>
     */
    public function getCompiled(): Collection
    {
        return $this->compiled;
    }

    public function setCompiled(Collection $compiled): void
    {
        if ($this->isPublished() && $this->compiled !== $compiled) {
            $this->getParent()->setLastModified(new DateTime());
        }

        $this->compiled = $compiled;
    }

    public function addCompiled(CompiledDataInterface $compiled): void
    {
        if (!$this->getCompiled()->contains($compiled)) {
            if ($this->isPublished()) {
                $this->getParent()->setLastModified(new DateTime());
            }
            $compiled->setVersion($this);
            $this->getCompiled()->add($compiled);

            if ($compiled->hasErrors()) {
                $this->setCompileErrors(true);
            }
        }
    }

    public function removeCompiled(CompiledDataInterface $compiled): void
    {
        if ($this->getCompiled()->contains($compiled)) {
            $this->getCompiled()->removeElement($compiled);
            $compiled->setVersion(null);

            // recalculate compile errors, without removed compiled data element
            $compileErrors = false;
            foreach ($this->getCompiled() as $c) {
                $compileErrors |= $c->hasErrors();
            }
            $this->setCompileErrors($compileErrors);
        }
    }

    public function cleanCompiled(): void
    {
        $this->getCompiled()->map(function (CompiledDataInterface $compiled): void {
            $this->removeCompiled($compiled);
        });
        $this->setCompileErrors(false);
    }

    public function hasCompileErrors(): bool
    {
        return $this->compileErrors;
    }

    public function setCompileErrors(bool $compileErrors): void
    {
        $this->compileErrors = $compileErrors;
    }
}
