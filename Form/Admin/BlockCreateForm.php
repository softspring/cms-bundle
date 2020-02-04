<?php

namespace Softspring\CmsBundle\Form\Admin;

use Symfony\Component\OptionsResolver\OptionsResolver;

class BlockCreateForm extends AbstractBlockForm implements BlockCreateFormInterface
{
    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'sfs_cms',
            'label_format' => 'admin_blocks.create.form.%name%.label',
        ]);
    }
}