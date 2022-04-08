<?php

namespace Softspring\CmsBundle\Form\Admin\Site;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @deprecated
 */
class SiteCreateForm extends AbstractSiteForm implements SiteCreateFormInterface
{
    
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'translation_domain' => 'sfs_cms_sites',
            'label_format' => 'admin_sites.create.form.%name%.label',
            'validation_groups' => ['Default', 'create'],
        ]);
    }
}
