<?php

namespace Softspring\CmsBundle\Form\Admin\Content;

use Softspring\CmsBundle\Form\Type\DynamicFormType;
use Softspring\CmsBundle\Model\ContentInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentCreateForm extends AbstractType implements ContentCreateFormInterface
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ContentInterface::class,
            'label_format' => 'admin_content.form.%name%.label',
            'validation_groups' => ['Default', 'create'],
            'translation_domain' => 'sfs_cms_admin',
            'content' => null,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class);

        if (!empty($options['content']['extra_fields'])) {
            $builder->add('extraData', DynamicFormType::class, [
                'form_fields' => $options['content']['extra_fields'],
            ]);
        }
    }
}
