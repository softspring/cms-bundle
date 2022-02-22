<?php

namespace Softspring\CmsBundle\Form\Admin\Page;

use Softspring\CmsBundle\Form\DynamicModuleCollectionType;
use Softspring\CmsBundle\Model\PageInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PageContent2Form extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PageInterface::class,
            'label_format' => 'admin_pages.form.%name%.label',
            'validation_groups' => ['Default', 'create', 'update'],
            'translation_domain' => 'sfs_cms',
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('dynamicModules', DynamicModuleCollectionType::class);
    }
}