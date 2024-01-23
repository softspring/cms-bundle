<?php

namespace Softspring\CmsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @deprecated use TranslatableType
 */
class TranslatableTextType extends AbstractType
{
    protected string $defaultLocale;
    protected array $enabledLocales;

    public function __construct(string $defaultLocale, array $enabledLocales)
    {
        $this->defaultLocale = $defaultLocale;
        $this->enabledLocales = $enabledLocales;
    }

    public function getBlockPrefix(): string
    {
        return 'translatable_text';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'required' => false,
            'languages' => $this->enabledLocales,
            'default_language' => $this->defaultLocale,
            'children_attr' => [],
        ]);

        $resolver->setRequired('languages');
        $resolver->setAllowedTypes('languages', 'array');
        $resolver->setRequired('default_language');
        $resolver->setAllowedTypes('default_language', 'string');
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        foreach ($options['languages'] as $lang) {
            $builder->add($lang, TextType::class, [
                'required' => $lang == $options['default_language'],
                'label' => $lang,
                'attr' => $options['children_attr'],
                'block_prefix' => 'translatable_text_element',
            ]);
        }
    }
}
