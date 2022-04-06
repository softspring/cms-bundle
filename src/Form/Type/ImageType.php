<?php

namespace Softspring\CmsBundle\Form\Type;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Softspring\ImageBundle\Model\ImageInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImageType extends AbstractType
{
    protected EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getParent(): ?string
    {
        return EntityType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'class' => ImageInterface::class,
            'em' => $this->em,
            'required' => false,
            'image_types' => [],
            'query_builder' => fn(EntityRepository $entityRepository) => $entityRepository->createQueryBuilder('i'),
            'choice_label' => function (ImageInterface $image) {
                return $image->getId();
            },
            'choice_filter' => function (?ImageInterface $image = null) {
                return true;
            },
            'choice_attr' => function (?ImageInterface $image = null) {
                return [

                ];
            },
        ]);

        $resolver->setDefault('query_builder', function (Options $options) {
            return function (EntityRepository $er) use ($options) {
                return $er->createQueryBuilder('i')
                    ->orderBy('i.id', 'ASC')
                    ->andWhere('i.type IN (:types)')
                    ->setParameter('types', $options['image_types']);
            };
        });
    }
}