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
use Symfony\Contracts\Translation\TranslatorInterface;

class SiteChoiceType extends AbstractType
{
    protected CmsConfig $cmsConfig;
    protected EntityManagerInterface $em;
    protected TranslatorInterface $translator;

    public function __construct(CmsConfig $cmsConfig, EntityManagerInterface $em, TranslatorInterface $translator)
    {
        $this->cmsConfig = $cmsConfig;
        $this->em = $em;
        $this->translator = $translator;
    }

    public function getParent(): string
    {
        return EntityType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'em' => $this->em,
            'class' => SiteInterface::class,
            'multiple' => true,
            'content' => null,
            'choice_label' => function (?SiteInterface $site) {
                return $site ? $this->translator->trans("$site.name", [], 'sfs_cms_sites') : '';
            },
        ]);

        $resolver->setNormalizer('choices', function (Options $options, $value) {
            if (empty($options['content'])) {
                $siteChoices = $this->cmsConfig->getSites();
            } elseif ($options['content'] instanceof ContentInterface) {
                $siteChoices = $options['content']->getSites()->toArray();
            } else {
                $siteChoices = $this->cmsConfig->getSitesForContent($options['content']['_id']);
            }

            $siteChoices = array_values($siteChoices);

            usort($siteChoices, function (SiteInterface $a, SiteInterface $b) {
                return ($a->getConfig()['extra']['order'] ?? 500) <=> ($b->getConfig()['extra']['order'] ?? 500);
            });

            return $siteChoices;
        });
    }
}
