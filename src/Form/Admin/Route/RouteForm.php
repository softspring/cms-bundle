<?php

namespace Softspring\CmsBundle\Form\Admin\Route;

use Doctrine\ORM\EntityManagerInterface;
use Softspring\CmsBundle\Model\PageInterface;
use Softspring\CmsBundle\Model\RouteInterface;
use Softspring\CmsBundle\Model\SiteInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class RouteForm extends AbstractType
{
    protected EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RouteInterface::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id', TextType::class);
        $builder->add('type', ChoiceType::class, [
            'choices' => [
                'PAGE' => RouteInterface::TYPE_PAGE,
                'CONTENT' => RouteInterface::TYPE_CONTENT,
                'STATIC' => RouteInterface::TYPE_STATIC,
                'NOT_FOUND' => RouteInterface::TYPE_NOT_FOUND,
                'REDIRECT_TO_ROUTE' => RouteInterface::TYPE_REDIRECT_TO_ROUTE,
                'REDIRECT_TO_URL' => RouteInterface::TYPE_REDIRECT_TO_URL,
                'PARENT_ROUTE' => RouteInterface::TYPE_PARENT_ROUTE,
            ]
        ]);

        // if ($this->manager->getEntityClassReflection()->implementsInterface(SiteReferenceInterface::class)) {
        $builder->add('site', EntityType::class, [
            'required' => false,
            'em' => $this->em,
            'class' => SiteInterface::class,
            'choice_label' => function (SiteInterface $site) {
                return $site->getName();
            },
        ]);
        // }

        $builder->add('page', EntityType::class, [
            'class' => PageInterface::class,
            'required' => false,
            'em' => $this->em,
            'choice_label' => function (PageInterface $page) {
                return $page->getName();
            },
            // 'constraints' => new NotBlank(),
        ]);

        $builder->add('paths', RoutePathCollectionType::class);
    }
}
