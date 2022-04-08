<?php

namespace Softspring\CmsBundle\Form\Admin\Block;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @deprecated
 */
class BlockUpdateForm extends BlockForm implements BlockUpdateFormInterface
{
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'translation_domain' => 'sfs_cms_blocks',
            'label_format' => 'admin_blocks.update.form.%name%.label',
            'validation_groups' => ['Default', 'update'],
        ]);
    }
}
