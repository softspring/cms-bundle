<?php

namespace Softspring\CmsBundle\Form\Admin\Route;

use Symfony\Component\OptionsResolver\OptionsResolver;

class RouteUpdateForm extends AbstractRouteForm
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
        $resolver->setDefault('disabled_id', true);
    }
}
