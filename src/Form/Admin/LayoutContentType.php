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
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $layoutConfig = $this->cmsConfig->getLayout($options['layout']);

        foreach ($layoutConfig['containers'] as $containerId => $containerConfig) {
            $builder->add($containerId, CmsModuleCollectionType::class, [
                'label' => "{$options['layout']}.$containerId.container_title",
                'translation_domain' => 'cms_layouts',
            ]);
        }
    }
}