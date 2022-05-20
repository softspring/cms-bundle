<?php

namespace Softspring\CmsBundle\Form\Admin;

use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Form\Module\ModuleCollectionType;
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
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $layoutConfig = $this->cmsConfig->getLayout($options['layout']);

        foreach ($layoutConfig['containers'] as $containerId => $containerConfig) {
            $builder->add($containerId, ModuleCollectionType::class, [
                'label' => "{$options['layout']}.$containerId.container_title",
                'translation_domain' => 'sfs_cms_layouts',
                'content_type' => $options['content_type'],
                // random prototype name to allow multiple levels
                'prototype_name' => '__'.substr(sha1(rand(0,10000000000)), rand(0,10), 8).'__',
                'module_collection_class' => 'container-fluid',
                'module_row_class' => 'row',
            ]);
        }
    }
}