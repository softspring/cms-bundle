<?php

namespace Softspring\CmsBundle\Form\Admin\Route;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\Valid;

class RoutePathCollectionType extends AbstractType
{
    public function getParent(): ?string
    {
        return CollectionType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'route_path_collection';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'entry_type' => RoutePathType::class,
            'required' => false,
            'constraints' => [new Count(['min' => 1]), new Valid()],
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'error_bubbling' => false,
        ]);
    }
}
