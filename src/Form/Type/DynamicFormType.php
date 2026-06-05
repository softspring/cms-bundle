<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Form\Type;

use Softspring\Component\DynamicFormType\Form\Type\DynamicFormType as BaseDynamicFormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DynamicFormType extends BaseDynamicFormType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'sfs_cms_modules',
        ]);

        $resolver->setDefault('form_template', null);
        $resolver->setAllowedTypes('form_template', ['null', 'string']);

        $resolver->setDefault('edit_template', null);
        $resolver->setAllowedTypes('edit_template', ['null', 'string']);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options): void {
            $data = $event->getData();

            if (null !== $data && !is_array($data)) {
                return;
            }

            $data ??= [];
            $defaults = $this->defaultValues($options['form_fields'] ?? []);

            foreach ($defaults as $field => $value) {
                if (!array_key_exists($field, $data)) {
                    $data[$field] = $value;
                }
            }

            $event->setData($data);
        });
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['form_template'] = $options['form_template'];
        $view->vars['edit_template'] = $options['edit_template'];
    }

    protected function defaultValues(array $formFields): array
    {
        $defaults = [];

        foreach ($formFields as $field => $config) {
            if (array_key_exists('default_value', $config['type_options'] ?? [])) {
                $defaults[$field] = $config['type_options']['default_value'];
            }
        }

        return $defaults;
    }
}
