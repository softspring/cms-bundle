<?php

namespace Softspring\CmsBundle\Entity\Embeddable;

use Doctrine\ORM\Mapping as ORM;
use Softspring\TranslationBundle\Configuration\Translation;

/**
 * @ORM\Embeddable
 */
class Seo
{
    /**
     * @ORM\Column(options={"default": false}, nullable=true)
     */
    protected bool $noIndex = false;

    /**
     * @ORM\Column(options={"default": false}, nullable=true)
     */
    protected bool $noFollow = false;

    /**
     * @ORM\Column(options={"default": true}, nullable=true)
     */
    protected bool $sitemap = true;

    /**
     * @ORM\Column(nullable=true)
     * @Translation()
     */
    protected ?string $metaTitle = null;

    /**
     * @ORM\Column(nullable=true)
     * @Translation()
     */
    protected ?string $metaDescription = null;

    /**
     * @ORM\Column(nullable=true)
     * @Translation()
     */
    protected ?string $metaKeywords = null;

    public function isNoIndex(): bool
    {
        return $this->noIndex;
    }

    public function setNoIndex(bool $noIndex): void
    {
        $this->noIndex = $noIndex;
    }

    public function isNoFollow(): bool
    {
        return $this->noFollow;
    }

    public function setNoFollow(bool $noFollow): void
    {
        $this->noFollow = $noFollow;
    }

    public function isSitemap(): bool
    {
        return $this->sitemap;
    }

    public function setSitemap(bool $sitemap): void
    {
        $this->sitemap = $sitemap;
    }

    public function getMetaTitle(): ?string
    {
        return $this->metaTitle;
    }

    public function setMetaTitle(?string $metaTitle): void
    {
        $this->metaTitle = $metaTitle;
    }

    public function getMetaDescription(): ?string
    {
        return $this->metaDescription;
    }

    public function setMetaDescription(?string $metaDescription): void
    {
        $this->metaDescription = $metaDescription;
    }

    public function getMetaKeywords(): ?string
    {
        return $this->metaKeywords;
    }

    public function setMetaKeywords(?string $metaKeywords): void
    {
        $this->metaKeywords = $metaKeywords;
    }
}
