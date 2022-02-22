<?php

namespace Softspring\CmsBundle\Entity\Modules;

use Doctrine\ORM\Mapping as ORM;
use Softspring\CmsBundle\Entity\AbstractModule;
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
    protected ?string $title = null;

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }
}
