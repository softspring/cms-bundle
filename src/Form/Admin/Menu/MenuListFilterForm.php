<?php

namespace Softspring\CmsBundle\Form\Admin\Menu;

use Softspring\CmsBundle\Model\MenuInterface;
use Softspring\Component\DoctrinePaginator\Form\PaginatorForm;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MenuListFilterForm extends PaginatorForm implements MenuListFilterFormInterface
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'translation_domain' => 'sfs_cms_admin',
            'label_format' => 'admin_menus.list.filter_form.%name%.label',
            'class' => MenuInterface::class,
            'rpp_valid_values' => [20],
            'rpp_default_value' => 20,
            'order_valid_fields' => ['name', 'type'],
            'order_default_value' => 'name',
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder->add('name', TextType::class, [
            'property_path' => '[name__like]',
        ]);
    }
}
