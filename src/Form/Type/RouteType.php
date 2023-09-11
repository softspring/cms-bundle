<?php

namespace Softspring\CmsBundle\Form\Type;

use Doctrine\ORM\EntityManagerInterface;
use Softspring\CmsBundle\Model\RouteInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RouteType extends AbstractType
{
    protected EntityManagerInterface $sfsRouteEm;

    public function __construct(EntityManagerInterface $sfsRouteEm)
    {
        $this->sfsRouteEm = $sfsRouteEm;
    }

    public function getParent(): ?string
    {
        return EntityType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class' => RouteInterface::class,
            'em' => $this->sfsRouteEm,
            'required' => false,
            'choice_label' => function (RouteInterface $route) {
                return $route->getId();
            },
        ]);
    }
}
