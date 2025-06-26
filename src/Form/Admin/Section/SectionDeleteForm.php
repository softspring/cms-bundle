<?php

namespace Softspring\CmsBundle\Form\Admin\Section;

use Doctrine\ORM\EntityManagerInterface;
use Softspring\CmsBundle\Model\SectionInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SectionDeleteForm extends AbstractType implements SectionDeleteFormInterface
{
    public function __construct(protected EntityManagerInterface $em)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SectionInterface::class,
            'validation_groups' => ['Default', 'delete'],
            'translation_domain' => 'sfs_cms_admin',
            'label_format' => 'admin_sections.delete.form.%name%.label',
        ]);
    }
}
