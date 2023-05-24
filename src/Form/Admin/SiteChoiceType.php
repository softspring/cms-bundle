<?php

namespace Softspring\CmsBundle\Form\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\SiteInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SiteChoiceType extends AbstractType
{
    protected CmsConfig $cmsConfig;
    protected EntityManagerInterface $em;

    public function __construct(CmsConfig $cmsConfig, EntityManagerInterface $em)
    {
        $this->cmsConfig = $cmsConfig;
        $this->em = $em;
    }

    public function getParent(): string
    {
        return EntityType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choice_translation_domain' => 'sfs_cms_sites',
            'em' => $this->em,
            'class' => SiteInterface::class,
            'multiple' => true,
            'content' => null,
        ]);

        $resolver->setNormalizer('choices', function (Options $options, $value) {
            if (empty($options['content'])) {
                $siteChoices = $this->cmsConfig->getSites();
            } elseif ($options['content'] instanceof ContentInterface) {
                $siteChoices = $options['content']->getSites()->toArray();
            } else {
                $siteChoices = $this->cmsConfig->getSitesForContent($options['content']['_id']);
            }

            return array_values($siteChoices);
        });
    }
}
