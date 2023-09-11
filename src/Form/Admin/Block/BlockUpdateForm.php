<?php

namespace Softspring\CmsBundle\Form\Admin\Block;

use Symfony\Component\OptionsResolver\OptionsResolver;

class BlockUpdateForm extends AbstractBlockForm implements BlockUpdateFormInterface
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'translation_domain' => 'sfs_cms_blocks',
            'label_format' => 'admin_blocks.update.form.%name%.label',
            'validation_groups' => ['Default', 'update'],
        ]);
    }
}
