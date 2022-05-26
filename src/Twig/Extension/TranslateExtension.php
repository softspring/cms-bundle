<?php

namespace Softspring\CmsBundle\Twig\Extension;

use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TranslateExtension extends AbstractExtension
{
    protected RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('sfs_cms_trans', [$this, 'translate'], ['safe'], ['is_safe' => ['html']]),
        ];
    }

    public function translate(array $translatableText): string
    {
        $request = $this->requestStack->getCurrentRequest();

        if (isset($translatableText[$request->getLocale()])) {
            return $translatableText[$request->getLocale()];
        }

        if (isset($translatableText[$request->getDefaultLocale()])) {
            return $translatableText[$request->getDefaultLocale()];
        }

        return '';
    }
}
