<?php

namespace Softspring\CmsBundle\Form\Admin\Block;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @deprecated
 */
class BlockCreateForm extends BlockForm implements BlockCreateFormInterface
{
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'translation_domain' => 'sfs_cms_blocks',
            'label_format' => 'admin_blocks.create.form.%name%.label',
        ]);
    }
}
