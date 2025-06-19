<?php

namespace Softspring\CmsBundle\Form\Admin\SectionVersion;

use Doctrine\ORM\EntityManagerInterface;
use Softspring\CmsBundle\Form\Module\ModuleCollectionType;
use Softspring\CmsBundle\Helper\CmsHelper;
use Softspring\CmsBundle\Helper\LocaleHelper;
use Softspring\CmsBundle\Model\SectionInterface;
use Softspring\CmsBundle\Model\SectionVersionInterface;
use Softspring\CmsBundle\Utils\Hash;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VersionCreateForm extends AbstractType implements VersionCreateFormInterface
{
    public function __construct(protected EntityManagerInterface $em, protected CmsHelper $cmsHelper, protected LocaleHelper $localeHelper)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SectionVersionInterface::class,
            'label_format' => 'admin_section.form.%name%.label',
            'validation_groups' => ['Default', 'create'],
            'translation_domain' => 'sfs_cms_sections',
        ]);

        $resolver->setRequired('section');
        $resolver->setAllowedTypes('section', [SectionInterface::class]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('data', ModuleCollectionType::class, [
            'label' => 'data.container_title',
            'translation_domain' => 'sfs_cms_layouts',
            // 'content_type' => $options['content_type'],
            // 'allowed_modules' => !empty($containerConfig['allowed_modules']) ? $containerConfig['allowed_modules'] : null,
            // random prototype name to allow multiple levels
            'prototype_name' => '__'.Hash::generate().'__',
            'module_collection_class' => 'container-fluid',
            'module_row_class' => 'row',
        ]);

        $builder->add('module_prototypes_collection', ModuleCollectionType::class, [
            'label' => '__label__',
            'block_prefix' => 'module_prototypes_collection',
            'translation_domain' => 'sfs_cms_layouts',
            'allowed_modules' => null,
            // random prototype name to allow multiple levels
            'prototype_name' => '___MODULE___',
            'module_collection_class' => 'container-fluid',
            'module_row_class' => 'row',
            'prototype' => true,
            'mapped' => false,
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['available_locales'] = $this->localeHelper->getEnabledLocales();
    }
}
