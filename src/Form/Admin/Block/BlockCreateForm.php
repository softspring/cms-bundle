<?php

namespace Softspring\CmsBundle\Form\Admin\Block;

use Symfony\Component\OptionsResolver\OptionsResolver;

class BlockCreateForm extends AbstractBlockForm implements BlockCreateFormInterface
{
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'translation_domain' => 'sfs_cms_admin',
            'label_format' => 'admin_blocks.create.form.%name%.label',
        ]);
    }
}
