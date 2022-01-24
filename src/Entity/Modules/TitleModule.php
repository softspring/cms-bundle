<?php

namespace Softspring\CmsBundle\Entity\Modules;

use Softspring\CmsBundle\Entity\AbstractModule;
use Doctrine\ORM\Mapping as ORM;
use Softspring\TranslationBundle\Configuration\Translatable;
use Softspring\TranslationBundle\Configuration\Translation;

/**
 * @ORM\Table(name="cms_module_title")
 * @ORM\Entity()
 * @Translatable(prefix="cms_module_title.")
 */
class TitleModule extends AbstractModule
{
    /**
     * @ORM\Column(name="title", type="text", nullable=true)
     * @Translation()
     */
    protected ?string $title;

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string|null $title
     */
    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }
}