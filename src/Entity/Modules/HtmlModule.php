<?php

namespace Softspring\CmsBundle\Entity\Modules;

use Doctrine\ORM\Mapping as ORM;
use Softspring\CmsBundle\Entity\AbstractModule;
use Softspring\TranslationBundle\Configuration\Translatable;
use Softspring\TranslationBundle\Configuration\TranslationsHtml;

/**
 * @ORM\Table(name="cms_module_html")
 * @ORM\Entity()
 * @Translatable(prefix="cms_module_html.")
 */
class HtmlModule extends AbstractModule
{
    /**
     * @ORM\Column(name="code", type="text", nullable=true)
     * @TranslationsHtml()
     */
    protected ?string $code = null;

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): void
    {
        $this->code = $code;
    }
}
