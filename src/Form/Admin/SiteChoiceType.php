<?php

namespace Softspring\CmsBundle\Form\Admin;

use Softspring\CmsBundle\Config\CmsConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SiteChoiceType extends AbstractType
{
    protected CmsConfig $cmsConfig;

    public function __construct(CmsConfig $cmsConfig)
    {
        $this->cmsConfig = $cmsConfig;
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choice_translation_domain' => 'sfs_cms_sites',
            'content' => null,
        ]);

        $resolver->setNormalizer('choices', function (Options $options, $value) {
            $siteChoices = $this->cmsConfig->getSitesForContent($options['content']['_id']);

            return array_combine(array_map(fn($key) => "$key.name", array_keys($siteChoices)), array_keys($siteChoices));
        });
    }
}