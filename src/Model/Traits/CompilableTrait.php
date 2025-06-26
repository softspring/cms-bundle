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
        if (!$this->compiled->contains($compiled)) {
            if ($this->isPublished()) {
                $this->getParent()->setLastModified(new DateTime());
            }
            $compiled->setVersion($this);
            $this->compiled->add($compiled);
        }
    }

    public function removeCompiled(CompiledDataInterface $compiled): void
    {
        if ($this->compiled->contains($compiled)) {
            $this->compiled->removeElement($compiled);
            $compiled->setVersion(null);
        }
    }

    public function cleanCompiled(): void
    {
        $this->compiled->map(function (CompiledDataInterface $compiled) {
            $this->removeCompiled($compiled);
        });
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
