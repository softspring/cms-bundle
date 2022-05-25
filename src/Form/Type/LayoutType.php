<?php

namespace Softspring\CmsBundle\Form\Type;

use Softspring\CmsBundle\Config\CmsConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LayoutType extends AbstractType
{
    protected CmsConfig $cmsConfig;

    public function __construct(CmsConfig $cmsConfig)
    {
        $this->cmsConfig = $cmsConfig;
    }

    public function getParent(): ?string
    {
        return ChoiceType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $layouts = array_keys($this->cmsConfig->getLayouts());

        $resolver->setDefaults([
            'choices' => array_combine(array_map(fn ($layout) => "$layout.title", $layouts), $layouts),
            'choice_translation_domain' => 'sfs_cms_layouts',
        ]);
    }
}
