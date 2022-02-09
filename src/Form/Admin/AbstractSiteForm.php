<?php

namespace Softspring\CmsBundle\Form\Admin;

use Softspring\CmsBundle\Manager\SiteManagerInterface;
use Softspring\CmsBundle\Model\SiteInterface;
use Softspring\CmsBundle\Model\SiteLanguagesInterface;
use Softspring\CmsBundle\Model\SiteSimpleCountriesInterface;
use Softspring\DoctrineTemplates\Model\NamedInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\LocaleType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractSiteForm extends AbstractType
{
    /**
     * @var SiteManagerInterface
     */
    protected $manager;

    /**
     * SiteCreateForm constructor.
     */
    public function __construct(SiteManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SiteInterface::class,
            'translation_domain' => 'sfs_cms',
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id');
        $builder->add('enabled', CheckboxType::class, ['required' => false]);

        if ($this->manager->getEntityClassReflection()->implementsInterface(NamedInterface::class)) {
            $builder->add('name', TextType::class, ['required' => false]);
        }

        if ($this->manager->getEntityClassReflection()->implementsInterface(SiteLanguagesInterface::class)) {
            $builder->add('languages', LocaleType::class, [
                'multiple' => true,
                'required' => false,
                'preferred_choices' => [
                    'es',
                    'en',
                ],
            ]);
        }

        if ($this->manager->getEntityClassReflection()->implementsInterface(SiteSimpleCountriesInterface::class)) {
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
