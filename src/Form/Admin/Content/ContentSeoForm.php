<?php

namespace Softspring\CmsBundle\Form\Admin\Content;

use Softspring\CmsBundle\Form\SeoType;
use Softspring\CmsBundle\Model\ContentInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentSeoForm extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ContentInterface::class,
            'label_format' => 'admin_pages.form.%name%.label',
            'validation_groups' => ['Default', 'create', 'update'],
            'translation_domain' => 'sfs_cms_admin',
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('seo', SeoType::class);
    }
}
