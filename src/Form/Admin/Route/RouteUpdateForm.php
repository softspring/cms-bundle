<?php

namespace Softspring\CmsBundle\Form\Admin\Route;

use Symfony\Component\Form\FormBuilderInterface;

class RouteUpdateForm extends AbstractRouteForm
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder->get('id')->setDisabled(true);
    }
}
