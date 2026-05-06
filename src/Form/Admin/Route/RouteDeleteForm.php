<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Form\Admin\Route;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RouteDeleteForm extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'validation_groups' => 'delete',
        ]);
    }
}
