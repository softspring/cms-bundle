<?php

namespace Softspring\CmsBundle\Form\Admin;

use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Form\Module\ModuleCollectionType;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Utils\Hash;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LayoutContentType extends AbstractType
{
    protected CmsConfig $cmsConfig;

    public function __construct(CmsConfig $cmsConfig)
    {
        $this->cmsConfig = $cmsConfig;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('layout');
        $resolver->setRequired('content_type');
        $resolver->setAllowedTypes('content_type', ['string']);
        $resolver->setRequired('content');
        $resolver->setAllowedTypes('content', [ContentInterface::class]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $layoutConfig = $this->cmsConfig->getLayout($options['layout']);

        foreach ($layoutConfig['containers'] as $containerId => $containerConfig) {
            $builder->add($containerId, ModuleCollectionType::class, [
                'label' => "{$options['layout']}.$containerId.container_title",
                'translation_domain' => 'sfs_cms_layouts',
                'content_type' => $options['content_type'],
                'allowed_modules' => !empty($containerConfig['allowed_modules']) ? $containerConfig['allowed_modules'] : null,
                // random prototype name to allow multiple levels
                'prototype_name' => '__'.Hash::generate().'__',
                'module_collection_class' => 'container-fluid',
                'module_row_class' => 'row',
                'content' => $options['content'],
            ]);
        }
    }
}
