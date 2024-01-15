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

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => array_keys($this->cmsConfig->getLayouts()),
            'choice_translation_domain' => 'sfs_cms_layouts',
        ]);

        $resolver->setNormalizer('choices', function (OptionsResolver $resolver, $choices) {
            return array_combine(array_map(fn ($layout) => "$layout.title", $choices), $choices);
        });
    }
}
