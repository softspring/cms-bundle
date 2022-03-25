<?php

namespace Softspring\CmsBundle\Form\Admin\Site;

use Softspring\CmsBundle\Model\SiteInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SiteDeleteForm extends AbstractType implements SiteDeleteFormInterface
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SiteInterface::class,
            'translation_domain' => 'sfs_cms_admin',
            'label_format' => 'admin_sites.delete.form.%name%.label',
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    }
}
