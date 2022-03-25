<?php

namespace Softspring\CmsBundle\Form\Type;

use Softspring\CmsBundle\Form\Admin\CmsModuleCollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContainerType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('allowed_modules');
        $resolver->setAllowedTypes('allowed_modules', 'array');
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('modules', CmsModuleCollectionType::class, [
            'allowed_modules' => $options['allowed_modules'],
        ]);
    }
}