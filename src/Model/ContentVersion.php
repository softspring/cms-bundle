<?php

namespace Softspring\CmsBundle\Model;

use Softspring\CmsBundle\Entity\Embeddable\Seo;

abstract class ContentVersion implements ContentVersionInterface
{
    protected ?ContentInterface $content = null;

    protected ?string $layout = null;

    protected ?int $createdAt = null;

//    protected ?Seo $seo = null;

    protected ?array $data = null;

    // protected ?string $compiled = null;

    public function getContent(): ?ContentInterface
    {
        return $this->content;
    }

    public function setContent(?ContentInterface $content): void
    {
        $this->content = $content;
    }

    public function getLayout(): ?string
    {
        return $this->layout;
    }

    public function setLayout(?string $layout): void
    {
        $this->layout = $layout;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt ? \DateTime::createFromFormat('U', $this->createdAt) : null;
    }

    public function autoSetCreatedAt()
    {
        $this->createdAt = time();
    }

//    public function getSeo(): ?Seo
//    {
//        return $this->seo;
//    }
//
//    public function setSeo(?Seo $seo): void
//    {
//        $this->seo = $seo;
//    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function setData(?array $data): void
    {
        $this->data = $data;
    }
}