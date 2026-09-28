<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Test\Unit\Form\Type;

use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Softspring\CmsBundle\Form\Type\DynamicFormType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DynamicFormTypeTest extends TestCase
{
    public function testConfigureOptions(): void
    {
        $resolver = new OptionsResolver();
        $type = new DynamicFormType();

        $type->configureOptions($resolver);

        $options = $resolver->resolve([]);

        $this->assertSame('sfs_cms_modules', $options['translation_domain']);
        $this->assertNull($options['form_template']);
        $this->assertNull($options['edit_template']);
    }

    public function testConfigureOptionsRejectsInvalidTemplates(): void
    {
        $resolver = new OptionsResolver();
        $type = new DynamicFormType();

        $type->configureOptions($resolver);

        $this->expectException(InvalidOptionsException::class);

        $resolver->resolve([
            'form_template' => [],
        ]);
    }

    public function testBuildViewAddsTemplateVariables(): void
    {
        $view = new FormView();
        $form = $this->createMock(FormInterface::class);
        $type = new DynamicFormType();

        $type->buildView($view, $form, [
            'form_template' => 'form.html.twig',
            'edit_template' => 'edit.html.twig',
        ]);

        $this->assertSame('form.html.twig', $view->vars['form_template']);
        $this->assertSame('edit.html.twig', $view->vars['edit_template']);
    }

    public function testDefaultValues(): void
    {
        $type = new DynamicFormType();
        $method = new ReflectionMethod($type, 'defaultValues');

        $this->assertSame([
            'enabled' => true,
            'title' => 'Default title',
        ], $method->invoke($type, [
            'enabled' => [
                'type_options' => [
                    'default_value' => true,
                ],
            ],
            'title' => [
                'type_options' => [
                    'default_value' => 'Default title',
                ],
            ],
            'description' => [
                'type_options' => [],
            ],
        ]));
    }
}
