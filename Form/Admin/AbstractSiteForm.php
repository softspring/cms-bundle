<?php

namespace Softspring\CmsBundle\Form\Admin;

use Softspring\DoctrineTemplates\Model\NamedInterface;
use Softspring\CmsBundle\Manager\SiteManagerInterface;
use Softspring\CmsBundle\Model\SiteInterface;
use Softspring\CmsBundle\Model\SiteLanguagesInterface;
use Softspring\CmsBundle\Model\SiteSimpleCountriesInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\LocaleType;
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
     *
     * @param SiteManagerInterface $manager
     */
    public function __construct(SiteManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SiteInterface::class,
            'translation_domain' => 'sfs_cms',
        ]);
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id');
        $builder->add('enabled');

        if ($this->manager->getEntityClassReflection()->implementsInterface(NamedInterface::class)) {
            $builder->add('name');
        }

        if ($this->manager->getEntityClassReflection()->implementsInterface(SiteLanguagesInterface::class)) {
            $builder->add('languages', LocaleType::class, [
                'multiple' => true,
            ]);
        }

        if ($this->manager->getEntityClassReflection()->implementsInterface(SiteSimpleCountriesInterface::class)) {
            $builder->add('countries', CountryType::class, [
                'multiple' => true,
            ]);
        }
    }
}