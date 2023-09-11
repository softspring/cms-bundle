<?php

namespace Softspring\CmsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MediaAltModalType extends AbstractType
{
    use PropagateLabelFormatTrait;

    public function getBlockPrefix(): string
    {
        return 'cms_media_alt_modal';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'media_type_options' => [],
            'alt' => true,
            'alt_type_options' => [],
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('media', MediaModalType::class, $options['media_type_options']);

        if ($options['alt']) {
            $builder->add('alt', TextType::class, $options['alt_type_options']);
        }
    }
}
