<?php

namespace Softspring\CmsBundle\Twig\Extension;

use Symfony\Component\Form\FormView;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class EditFormExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('sfs_cms_form_view_set_attr', [$this, 'formViewSetAttr']),
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('sfs_cms_sha1', [$this, 'sha1']),
        ];
    }

    public function formViewSetAttr(FormView $formView, string $name, string $value): void
    {
        $formView->vars['attr'][$name] = $value;
    }

    public function sha1($value): string
    {
        return sha1($value);
    }
}
