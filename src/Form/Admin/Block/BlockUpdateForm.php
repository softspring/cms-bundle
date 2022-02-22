<?php

namespace Softspring\CmsBundle\Form\Admin\Block;

use Symfony\Component\OptionsResolver\OptionsResolver;

class BlockUpdateForm extends AbstractBlockForm implements BlockUpdateFormInterface
{
    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'translation_domain' => 'sfs_cms',
            'label_format' => 'admin_blocks.update.form.%name%.label',
            'validation_groups' => ['Default', 'update'],
        ]);
    }
}