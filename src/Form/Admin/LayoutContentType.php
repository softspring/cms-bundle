<?php

namespace Softspring\CmsBundle\Form\Admin;

use Softspring\CmsBundle\Config\CmsConfig;
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
            $builder->add($containerId, CmsModuleCollectionType::class, [
                'label' => "{$options['layout']}.$containerId.container_title",
                'translation_domain' => 'sfs_cms_layouts',
                'content_type' => $options['content_type'],
            ]);
        }
    }
}