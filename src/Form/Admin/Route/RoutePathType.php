<?php

namespace Softspring\CmsBundle\Form\Admin\Route;

use Softspring\CmsBundle\Manager\RoutePathManagerInterface;
use Softspring\CmsBundle\Model\RoutePathInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class RoutePathType extends AbstractType
{
    protected RoutePathManagerInterface $routePathManager;

    public function __construct(RoutePathManagerInterface $routePathManager)
    {
        $this->routePathManager = $routePathManager;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RoutePathInterface::class,
            'empty_data' => $this->routePathManager->createEntity(),
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('path', TextType::class, [
            'constraints' => new NotBlank(),
            'attr' => [
                'data-route-path' => true,
                'class' => 'sluggize',
            ]
        ]);
        $builder->add('cacheTtl', IntegerType::class);
        $builder->add('locale', ChoiceType::class, [
            'required' => false,
            'choices' => [
                // TODO GET FROM SITE
                'es' => 'es',
                'en' => 'en',
            ],
        ]);
    }
}
