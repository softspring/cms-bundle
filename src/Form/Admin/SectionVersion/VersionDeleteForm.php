<?php

namespace Softspring\CmsBundle\Form\Admin\SectionVersion;

use Doctrine\ORM\EntityManagerInterface;
use Softspring\CmsBundle\Model\SectionVersionInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VersionDeleteForm extends AbstractType implements VersionDeleteFormInterface
{
    public function __construct(protected EntityManagerInterface $em)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SectionVersionInterface::class,
            'validation_groups' => ['Default', 'delete'],
            'translation_domain' => 'sfs_cms_admin',
            'label_format' => 'admin_section_version.form.%name%.label',
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
    }
}
