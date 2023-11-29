<?php

namespace Softspring\CmsBundle\Twig\Extension;

use Symfony\Component\Form\FormView;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AccordionExtension extends AbstractExtension
{
    public function __construct(
        protected TranslatorInterface $translator
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('sfs_cms_admin_module_accordion_block_start', [$this, 'accordionStart'], ['is_safe' => ['html']]),
            new TwigFunction('sfs_cms_admin_module_accordion_block_end', [$this, 'accordionEnd'], ['is_safe' => ['html']]),
        ];
    }

    public function accordionStart(FormView $formView, string $accordionId, string $name = null, bool $open = false, string $title = null, bool $row = true): string
    {
        $id = uniqid('accordion-');
        $collapsed = $open ? '' : 'collapsed';
        $expanded = $open ? 'true' : 'false';
        $show = $open ? 'show' : '';
        $title = $title ?? $this->translator->trans($formView->vars['module_id'].'.form._group.'.($name ?? 'default'), [], 'sfs_cms_modules');
        $rowStart = $row ? '<div class="row">' : '';
        $bsParent = $accordionId ? "data-bs-parent=\"#$accordionId\"" : '';

        return <<<HTML
<div class="accordion-item">
    <h2 class="accordion-header" id="$id-heading">
        <button class="accordion-button $collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#$id-collapse"
                aria-expanded="$expanded" aria-controls="$id-collapse">$title</button>
    </h2>
    <div id="$id-collapse" class="accordion-collapse collapse $show" aria-labelledby="$id-heading" $bsParent>
        <div class="accordion-body">
            $rowStart
HTML;
    }

    public function accordionEnd(bool $row = true): string
    {
        $rowEnd = $row ? '</div>' : '';

        return <<<HTML
            $rowEnd
        </div>
    </div>
</div>
HTML;
    }
}
