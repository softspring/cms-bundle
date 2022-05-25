<?php

namespace Softspring\CmsBundle\Form\Admin\Content;

use Softspring\CmsBundle\Form\Type\DynamicFormType;
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
            'label_format' => 'admin_content.form.%name%.label',
            'validation_groups' => ['Default', 'create', 'update'],
            'translation_domain' => 'sfs_cms_contents',
            'content' => null,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('seo', DynamicFormType::class, [
            'form_fields' => $options['content']['seo'] ?? [],
        ]);
    }
}
