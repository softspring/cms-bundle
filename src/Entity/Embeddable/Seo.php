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

    /**
     * @return bool
     */
    public function isNoIndex(): bool
    {
        return $this->noIndex;
    }

    /**
     * @param bool $noIndex
     */
    public function setNoIndex(bool $noIndex): void
    {
        $this->noIndex = $noIndex;
    }

    /**
     * @return bool
     */
    public function isNoFollow(): bool
    {
        return $this->noFollow;
    }

    /**
     * @param bool $noFollow
     */
    public function setNoFollow(bool $noFollow): void
    {
        $this->noFollow = $noFollow;
    }

    /**
     * @return bool
     */
    public function isSitemap(): bool
    {
        return $this->sitemap;
    }

    /**
     * @param bool $sitemap
     */
    public function setSitemap(bool $sitemap): void
    {
        $this->sitemap = $sitemap;
    }

    /**
     * @return string|null
     */
    public function getMetaTitle(): ?string
    {
        return $this->metaTitle;
    }

    /**
     * @param string|null $metaTitle
     */
    public function setMetaTitle(?string $metaTitle): void
    {
        $this->metaTitle = $metaTitle;
    }

    /**
     * @return string|null
     */
    public function getMetaDescription(): ?string
    {
        return $this->metaDescription;
    }

    /**
     * @param string|null $metaDescription
     */
    public function setMetaDescription(?string $metaDescription): void
    {
        $this->metaDescription = $metaDescription;
    }

    /**
     * @return string|null
     */
    public function getMetaKeywords(): ?string
    {
        return $this->metaKeywords;
    }

    /**
     * @param string|null $metaKeywords
     */
    public function setMetaKeywords(?string $metaKeywords): void
    {
        $this->metaKeywords = $metaKeywords;
    }
}