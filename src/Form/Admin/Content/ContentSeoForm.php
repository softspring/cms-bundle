<?php

namespace Softspring\CmsBundle\Form\Admin\Content;

use Softspring\CmsBundle\Form\Type\DynamicFormType;
use Softspring\CmsBundle\Model\ContentInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentSeoForm extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ContentInterface::class,
            'label_format' => 'admin_content.form.%name%.label',
            'validation_groups' => ['Default', 'create', 'update'],
            'translation_domain' => 'sfs_cms_contents',
        ]);

        $resolver->setRequired('content_config');

        $resolver->setNormalizer('label_format', function (Options $options, $value) {
            return "admin_{$options['content_config']['_id']}.form.%name%.label";
        });
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('seo', DynamicFormType::class, [
            'form_fields' => $options['content_config']['seo'] ?? [],
            'translation_domain' => 'sfs_cms_contents',
            'label_format' => "admin_{$options['content_config']['_id']}.form.seo.%name%.label",
        ]);
    }
}
