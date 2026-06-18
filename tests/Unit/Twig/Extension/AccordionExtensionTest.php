<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Test\Unit\Twig\Extension;

use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Twig\Extension\AccordionExtension;
use Symfony\Component\Form\FormView;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\TwigFunction;

class AccordionExtensionTest extends TestCase
{
    public function testItRegistersFunctions(): void
    {
        $extension = new AccordionExtension($this->createMock(TranslatorInterface::class));

        self::assertSame([
            'sfs_cms_admin_module_accordion_block_start',
            'sfs_cms_admin_module_accordion_block_end',
        ], array_map(static fn (TwigFunction $function): string => $function->getName(), $extension->getFunctions()));
    }

    public function testItRendersAccordionStartAndEnd(): void
    {
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->expects($this->once())
            ->method('trans')
            ->with('hero.form._group.default', [], 'sfs_cms_modules')
            ->willReturn('Hero group');

        $view = new FormView();
        $view->vars['module_id'] = 'hero';

        $extension = new AccordionExtension($translator);

        $start = $extension->accordionStart($view, 'module-accordion', open: true, alwaysopen: false);
        $end = $extension->accordionEnd();

        self::assertStringContainsString('accordion-item', $start);
        self::assertStringContainsString('aria-expanded="true"', $start);
        self::assertStringContainsString('data-bs-parent="#module-accordion"', $start);
        self::assertStringContainsString('<strong class="text-uppercase">Hero group</strong>', $start);
        self::assertStringContainsString('<div class="row">', $start);
        self::assertStringContainsString('</div>', $end);
    }

    public function testItUsesProvidedTitleAndCanSkipRowWrapper(): void
    {
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->expects($this->never())->method('trans');

        $view = new FormView();
        $view->vars['module_id'] = 'hero';

        $extension = new AccordionExtension($translator);
        $start = $extension->accordionStart($view, 'accordion', title: 'Custom title', row: false);

        self::assertStringContainsString('Custom title', $start);
        self::assertStringNotContainsString('<div class="row">', $start);
        self::assertStringContainsString('collapsed', $start);
        self::assertStringContainsString('aria-expanded="false"', $start);
        self::assertStringNotContainsString('data-bs-parent', $start);
    }
}
