<?php

namespace Softspring\CmsBundle\Form\Admin\Site;

use Softspring\CmsBundle\Model\SiteInterface;
use Softspring\Component\DoctrinePaginator\Form\PaginatorForm;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SiteListFilterForm extends PaginatorForm implements SiteListFilterFormInterface
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'translation_domain' => 'sfs_cms_sites',
            'label_format' => 'admin_sites.list.filter_form.%name%.label',
            'class' => SiteInterface::class,
            'order_valid_fields' => ['id'],
            'order_default_value' => 'id',
        ]);
    }
}
