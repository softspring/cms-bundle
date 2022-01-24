<?php

namespace Softspring\CmsBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;

class RoutePathCollectionType extends AbstractType
{
    public function getParent()
    {
        return CollectionType::class;
    }

    public function getBlockPrefix()
    {
        return 'route_path_collection';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'entry_type' => RoutePathType::class,
            'required' => false,
            'constraints' => new Count(['min' => 1]),
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
        ]);
    }
}