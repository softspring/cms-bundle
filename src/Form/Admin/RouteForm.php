<?php

namespace Softspring\CmsBundle\Form\Admin;

use Softspring\CmsBundle\Model\PageInterface;
use Softspring\CmsBundle\Model\RouteInterface;
use Softspring\CmsBundle\Model\SiteInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class RouteForm extends AbstractType
{


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RouteInterface::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id', TextType::class);

        // if ($this->manager->getEntityClassReflection()->implementsInterface(SiteReferenceInterface::class)) {
        $builder->add('site', EntityType::class, [
            //'class' => SiteInterface::class,
            'required' => false,
            'class' => SiteInterface::class,
            'choice_label' => function (SiteInterface $site) {
                return $site->getName();
            }
        ]);
        //}

        $builder->add('page', EntityType::class, [
            'class' => PageInterface::class,
            'required' => false,
            'choice_label' => function (PageInterface $page) {
                return $page->getName();
            },
            'constraints' => new NotBlank(),
        ]);

        $builder->add('paths', RoutePathCollectionType::class);
    }
}