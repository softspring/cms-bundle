<?php

namespace Softspring\CmsBundle\Form\Admin\Content;

use Softspring\Component\CrudlController\Form\EntityListFilterForm;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentListFilterForm extends EntityListFilterForm
{
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'translation_domain' => 'sfs_cms_contents',
            'content_config' => null,
        ]);

        $resolver->setNormalizer('label_format', function (Options $options, $value) {
            return "admin_{$options['content_config']['_id']}.list.filter_form.%name%.label";
        });
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
    }

    public static function orderValidFields(): array
    {
        return ['name'];
    }

    public static function orderDefaultField(): string
    {
        return 'name';
    }
}
