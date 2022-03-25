<?php

namespace Softspring\CmsBundle\Form\Admin\Site;

use Softspring\CrudlBundle\Form\EntityListFilterForm;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SiteListFilterForm extends EntityListFilterForm implements SiteListFilterFormInterface
{
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'translation_domain' => 'sfs_cms_admin',
            'label_format' => 'admin_sites.list.filter_form.%name%.label',
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
    }
}
