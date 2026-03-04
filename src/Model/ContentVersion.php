<?php

namespace Softspring\CmsBundle\Model;

use InvalidArgumentException;
use Softspring\CmsBundle\Model\Traits\CompilableTrait;
use Softspring\CmsBundle\Model\Traits\ContentDataTrait;
use Softspring\CmsBundle\Model\Traits\VersionTrait;

abstract class ContentVersion implements ContentVersionInterface
{
    use ContentDataTrait;
    use CompilableTrait;
    use VersionTrait;

    protected ?ContentInterface $content = null;

    protected ?string $layout = null;

    protected ?array $seo = null;

    public function getContent(): ?ContentInterface
    {
        return $this->content;
    }

    public function setContent(?ContentInterface $content): void
    {
        $this->content = $content;
    }

    public function setParent(?VersionableInterface $parent): void
    {
        if ($parent && !$parent instanceof ContentInterface) {
            throw new InvalidArgumentException('Parent must implement ContentInterface');
        }
        $this->setContent($parent);
    }

    public function getParent(): ?VersionableInterface
    {
        return $this->getContent();
    }

    public function getLayout(): ?string
    {
        return $this->layout;
    }

    public function setLayout(?string $layout): void
    {
        $this->layout = $layout;
    }

    public function getSeo(): ?array
    {
        return $this->seo;
    }

    public function setSeo(?array $seo): void
    {
        $this->seo = $seo;
    }
}
