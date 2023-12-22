<?php

namespace Softspring\CmsBundle\Form\Admin\Block;

use Softspring\CmsBundle\Model\BlockInterface;
use Softspring\Component\DoctrinePaginator\Form\PaginatorForm;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BlockListFilterForm extends PaginatorForm implements BlockListFilterFormInterface
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'translation_domain' => 'sfs_cms_blocks',
            'label_format' => 'admin_blocks.list.filter_form.%name%.label',
            'class' => BlockInterface::class,
            'order_valid_fields' => ['name', 'type'],
            'order_default_value' => 'name',
        ]);
    }
}
