<?php

namespace Softspring\CmsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @deprecated use TranslatableType
 */
class TranslatableImageType extends AbstractType
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
        return 'translatable_image';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'required' => false,
            'languages' => $this->enabledLocales,
            'default_language' => $this->defaultLocale,
            'children_attr' => [],
            'media_attr' => [],
            'media_types' => [],
        ]);

        $resolver->setRequired('languages');
        $resolver->setAllowedTypes('languages', 'array');
        $resolver->setRequired('default_language');
        $resolver->setAllowedTypes('default_language', 'string');
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($options['languages'] as $lang) {
            $builder->add($lang, ImageType::class, [
                'required' => false, // $lang == $options['default_language'],
                'label' => $lang,
                'media_types' => $options['media_types'],
                'media_attr' => $options['media_attr'],
                'attr' => $options['children_attr'],
                'block_prefix' => 'translatable_media_element',
            ]);
        }
    }
}
