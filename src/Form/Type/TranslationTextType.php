<?php

namespace Softspring\CmsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TranslationTextType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('languages');
        $resolver->setAllowedTypes('languages', 'array');
        $resolver->setRequired('default_language');
        $resolver->setAllowedTypes('default_language', 'string');
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($options['languages'] as $lang) {
            $builder->add($lang, TextType::class, [
                'required' => $lang == $options['default_language'],
            ]);
        }
    }
}