<?php

namespace Softspring\CmsBundle\Form\Admin\Site;

use Softspring\CmsBundle\Manager\SiteManagerInterface;
use Softspring\CmsBundle\Model\SiteInterface;
use Softspring\CmsBundle\Model\SiteLanguagesInterface;
use Softspring\CmsBundle\Model\SiteSelectorHostInterface;
use Softspring\CmsBundle\Model\SiteSelectorPathInterface;
use Softspring\CmsBundle\Model\SiteSimpleCountriesInterface;
use Softspring\DoctrineTemplates\Model\NamedInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\LocaleType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @deprecated
 */
abstract class AbstractSiteForm extends AbstractType
{
    protected SiteManagerInterface $manager;

    public function __construct(SiteManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SiteInterface::class,
            'translation_domain' => 'sfs_cms_sites',
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $siteClass = $this->manager->getEntityClassReflection();

        $builder->add('id');
        $builder->add('enabled', CheckboxType::class, ['required' => false]);

        if ($siteClass->implementsInterface(NamedInterface::class)) {
            $builder->add('name', TextType::class, ['required' => false]);
        }

        if ($siteClass->implementsInterface(SiteSelectorPathInterface::class)) {
            $builder->add('path', TextType::class, ['required' => false]);
        }

        if ($siteClass->implementsInterface(SiteSelectorHostInterface::class)) {
            $builder->add('host', TextType::class, ['required' => false]);
        }

        if ($siteClass->implementsInterface(SiteLanguagesInterface::class)) {
            $builder->add('languages', LocaleType::class, [
                'multiple' => true,
                'required' => false,
                'preferred_choices' => [
                    'es',
                    'en',
                ],
            ]);
        }

        if ($siteClass->implementsInterface(SiteSimpleCountriesInterface::class)) {
            $builder->add('countries', CountryType::class, [
                'multiple' => true,
                'required' => false,
                'preferred_choices' => [
                    'ES',
                ],
            ]);
        }
    }
}
