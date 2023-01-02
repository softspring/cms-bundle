<?php

namespace Softspring\CmsBundle\Form\Admin\Route;

use Softspring\Component\DoctrinePaginator\Form\PaginatorFiltersForm;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RouteListFilterForm extends PaginatorFiltersForm implements RouteListFilterFormInterface
{
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'translation_domain' => 'sfs_cms_admin',
            'label_format' => 'admin_routes.list.filter_form.%name%.label',
            'rpp_valid_values' => [20],
            'rpp_default_value' => 20,
            'order_valid_fields' => ['id'],
            'order_default_value' => 'id',
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
    }
}
