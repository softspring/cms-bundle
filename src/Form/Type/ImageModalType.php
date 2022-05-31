<?php

namespace Softspring\CmsBundle\Form\Type;

use Softspring\ImageBundle\Form\ImageModalType as RealImageModalType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImageModalType extends AbstractType
{
    public function getBlockPrefix():string
    {
        return 'cms_image_modal';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'image_type_options' => [],
            'alt' => true,
            'alt_type_options' => [],
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('image', RealImageModalType::class, $options['image_type_options']);

        if ($options['alt']) {
            $builder->add('alt', TextType::class, $options['alt_type_options']);
        }
    }
}