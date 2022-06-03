<?php

namespace Softspring\CmsBundle\Form\Type;

use Softspring\Component\DynamicFormType\Form\DynamicFormCollectionType as BaseDynamicFormCollectionType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DynamicFormCollectionType extends BaseDynamicFormCollectionType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'entry_type' => DynamicFormType::class,
        ]);
    }
}
