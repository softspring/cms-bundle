<?php

namespace Softspring\CmsBundle\Form\Admin\Section;

use Softspring\CmsBundle\Model\SectionInterface;
use Softspring\CmsBundle\Translator\TranslatableContext;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Intl\Locales;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SectionCreateForm extends AbstractType implements SectionCreateFormInterface
{
    public function __construct(protected TranslatableContext $translatableContext)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SectionInterface::class,
            'validation_groups' => ['Default', 'create'],
            'translation_domain' => 'sfs_cms_admin',
            'label_format' => 'admin_sections.form.%name%.label',
        ]);

        $resolver->setDefault('default_locale', $this->translatableContext->getDefaultLocale());
        $resolver->setRequired('default_locale');
        $resolver->setAllowedTypes('default_locale', ['string']);

        $resolver->setDefault('locales', $this->translatableContext->getEnabledLocales());
        $resolver->setRequired('locales');
        $resolver->setAllowedTypes('locales', ['array']);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('name', TextType::class);

        $builder->add('ttl', NumberType::class, [
            'property_path' => 'extraData[ttl]',
            'required' => false,
            'html5' => true,
            'attr' => [
                'min' => 0,
                'step' => 1,
            ],
        ]);

        $builder->add('defaultLocale', ChoiceType::class, [
            'choice_translation_domain' => false,
            'choices' => array_combine(array_map(fn ($lang) => Locales::getName($lang), $options['locales']), $options['locales']),
            'default_value' => $options['default_locale'],
        ]);

        $builder->add('locales', ChoiceType::class, [
            'multiple' => true,
            'expanded' => true,
            'choice_translation_domain' => false,
            'choices' => array_combine(array_map(fn ($lang) => Locales::getName($lang), $options['locales']), $options['locales']),
            'default_value' => [$options['default_locale']],
        ]);
    }
}
