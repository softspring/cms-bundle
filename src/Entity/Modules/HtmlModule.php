<?php

namespace Softspring\CmsBundle\Entity\Modules;

use Softspring\CmsBundle\Entity\AbstractModule;
use Doctrine\ORM\Mapping as ORM;
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
    protected ?string $code;

    /**
     * @return string|null
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @param string|null $code
     */
    public function setCode(?string $code): void
    {
        $this->code = $code;
    }
}