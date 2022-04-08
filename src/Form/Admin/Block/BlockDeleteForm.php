<?php

namespace Softspring\CmsBundle\Form\Admin\Block;

use Softspring\CmsBundle\Model\BlockInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BlockDeleteForm extends AbstractType implements BlockDeleteFormInterface
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BlockInterface::class,
            'translation_domain' => 'sfs_cms_blocks',
            'label_format' => 'admin_blocks.delete.form.%name%.label',
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    }
}
