<?php

namespace Softspring\CmsBundle\Twig\Extension;

use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class SiteExtension extends AbstractExtension
{
    protected TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function getFilters()
    {
        return [
            new TwigFilter('sfs_cms_site_name', [$this, 'siteName']),
        ];
    }

    public function siteName(string $key): string
    {
        return $this->translator->trans("$key.name", [], 'sfs_cms_sites');
    }
}
