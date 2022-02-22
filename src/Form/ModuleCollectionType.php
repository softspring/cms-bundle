<?php

namespace Softspring\CmsBundle\Form;

use Doctrine\ORM\EntityManagerInterface;
use Softspring\CmsBundle\Entity\AbstractModule;
use Softspring\PolymorphicFormType\Form\Type\DoctrinePolymorphicCollectionType;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ModuleCollectionType extends DoctrinePolymorphicCollectionType
{
    protected EntityManagerInterface $em;

    protected array $typesMap;

    public function __construct(FormFactory $formFactory = null, EntityManagerInterface $em = null, array $typesMap = [])
    {
        parent::__construct($formFactory, $em);
        $this->typesMap = $typesMap;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'translation_domain' => 'sfs_cms',
            'abstract_class' => AbstractModule::class,
            'error_bubbling' => false,
            'entity_manager' => null,
            'types_map' => $this->typesMap,
        ]);
    }
}
