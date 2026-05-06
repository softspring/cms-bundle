<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Form\Admin\Block;

use Softspring\CmsBundle\Form\Type\BlockTypeType;
use Softspring\CmsBundle\Model\BlockInterface;
use Softspring\Component\DoctrinePaginator\Form\PaginatorForm;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BlockListFilterForm extends PaginatorForm implements BlockListFilterFormInterface
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'translation_domain' => 'sfs_cms_admin',
            'label_format' => 'admin_blocks.list.filter_form.%name%.label',
            'class' => BlockInterface::class,
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

        $builder->add('type', BlockTypeType::class);
    }
}
