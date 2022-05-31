<?php

namespace Softspring\CmsBundle\Form\Type;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Softspring\CmsBundle\Model\BlockInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BlockType extends AbstractType
{
    protected EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getParent(): string
    {
        return EntityType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'class' => BlockInterface::class,
            'em' => $this->em,
            'required' => false,
            'block_types' => [],
            'query_builder' => fn (EntityRepository $entityRepository) => $entityRepository->createQueryBuilder('b'),
            'choice_label' => function (BlockInterface $block) {
                return $block->getName();
            },
            'choice_filter' => function (?BlockInterface $block = null) {
                return true;
            },
        ]);

        $resolver->setDefault('query_builder', function (Options $options) {
            return function (EntityRepository $er) use ($options) {
                return $er->createQueryBuilder('b')
                    ->orderBy('b.id', 'ASC')
                    ->andWhere('b.type IN (:types)')
                    ->setParameter('types', $options['block_types']);
            };
        });
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
//        $view->vars['block_attr'] = $options['block_attr'];
    }
}