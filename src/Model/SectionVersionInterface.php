<?php

namespace Softspring\CmsBundle\Model;

interface SectionVersionInterface extends VersionInterface, CompilableInterface, ContentDataInterface
{
    public function getSection(): ?SectionInterface;

    public function setSection(?SectionInterface $section): void;
}
