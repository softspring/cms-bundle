<?php

namespace Softspring\CmsBundle\Form\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Softspring\CmsBundle\Helper\CmsHelper;
use Softspring\CmsBundle\Model\SiteInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class SiteChoiceType extends AbstractType
{
    protected CmsHelper $cmsHelper;
    protected EntityManagerInterface $em;
    protected TranslatorInterface $translator;

    public function __construct(CmsHelper $cmsHelper, EntityManagerInterface $em, TranslatorInterface $translator)
    {
        $this->cmsHelper = $cmsHelper;
        $this->em = $em;
        $this->translator = $translator;
    }

    public function getParent(): string
    {
        return EntityType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'em' => $this->em,
            'class' => SiteInterface::class,
            'multiple' => true,
            'expanded' => true,
            'content' => null,
            'choice_label' => function (?SiteInterface $site) {
                return $site ? $this->translator->trans("$site.name", [], 'sfs_cms_sites') : '';
            },
            'default_value' => null,
        ]);

        $resolver->setNormalizer('choices', function (Options $options, $value) {
            return $this->cmsHelper->site()->normalizeFormAvailableSites($value, $options['content']);
        });

        $resolver->setNormalizer('default_value', function (Options $options, $value) {
            if ($options['multiple']) {
                return $this->cmsHelper->site()->normalizeFormAvailableSites($value, $options['content']);
            }

            return null;
        });
    }
}
