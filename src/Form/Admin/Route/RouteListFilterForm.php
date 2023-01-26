<?php

namespace Softspring\CmsBundle\Form\Admin\Route;

use Doctrine\ORM\EntityManagerInterface;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Form\Admin\SiteChoiceType;
use Softspring\CmsBundle\Model\RouteInterface;
use Softspring\Component\DoctrinePaginator\Form\PaginatorForm;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RouteListFilterForm extends PaginatorForm implements RouteListFilterFormInterface
{
    protected CmsConfig $cmsConfig;

    public function __construct(EntityManagerInterface $em, CmsConfig $cmsConfig)
    {
        parent::__construct($em);
        $this->cmsConfig = $cmsConfig;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'translation_domain' => 'sfs_cms_admin',
            'label_format' => 'admin_routes.list.filter_form.%name%.label',
            'class' => RouteInterface::class,
            'rpp_valid_values' => [20],
            'rpp_default_value' => 20,
            'order_valid_fields' => ['id'],
            'order_default_value' => 'id',
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('id', TextType::class, [
            'property_path' => '[id__like]',
        ]);

        if (sizeof($this->cmsConfig->getSites()) > 1) {
            $builder->add('site', SiteChoiceType::class, [
                'required' => false,
                'property_path' => '[site]',
            ]);
        }
    }
}
