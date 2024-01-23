<?php

namespace Softspring\CmsBundle\Form\Type;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Model\BlockInterface;
use Softspring\CmsBundle\Render\BlockRenderer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BlockInstanceType extends AbstractType
{
    protected EntityManagerInterface $em;
    protected CmsConfig $cmsConfig;
    protected BlockRenderer $blockRenderer;

    public function __construct(EntityManagerInterface $em, CmsConfig $cmsConfig, BlockRenderer $blockRenderer)
    {
        $this->em = $em;
        $this->cmsConfig = $cmsConfig;
        $this->blockRenderer = $blockRenderer;
    }

    public function getBlockPrefix(): string
    {
        return 'block_instance';
    }

    public function getParent(): string
    {
        return EntityType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class' => BlockInterface::class,
            'em' => $this->em,
            'required' => false,
            'block_types' => null,
            'query_builder' => fn (EntityRepository $entityRepository) => $entityRepository->createQueryBuilder('b'),
            'choice_label' => function (BlockInterface $block) {
                return $block->getName();
            },
            'choice_filter' => function (BlockInterface $block = null) {
                return true;
            },
            'choice_attr' => function (?BlockInterface $block) {
                $attr = [
                    'data-block-preview' => $block ? $this->blockRenderer->renderBlock($block) : '',
                ];

                if ($block) {
                    $blockConfig = $this->cmsConfig->getBlock($block->getType());
                    $blockConfig['esi'] && $attr['data-block-esi'] = '';
                    $blockConfig['singleton'] && $attr['data-block-singleton'] = '';
                    $blockConfig['schedulable'] && $attr['data-block-schedulable'] = '';
                    $blockConfig['cache_ttl'] && $attr['data-block-cache-ttl'] = '';
                }

                return $attr;
            },
        ]);

        $resolver->addAllowedTypes('block_types', ['null', 'array', 'string']);
        $resolver->setNormalizer('block_types', function (Options $options, $value) {
            return is_string($value) ? [$value] : $value;
        });

        $resolver->setDefault('query_builder', function (Options $options) {
            $blockTypes = $options['block_types'];

            if (null === $blockTypes) {
                $blockTypes = array_keys($this->cmsConfig->getBlocks());
            }

            $blockTypes = array_filter($blockTypes, function ($blockType) {
                $config = $this->cmsConfig->getBlock($blockType);

                return $config && !$config['static'];
            });

            return function (EntityRepository $er) use ($blockTypes) {
                return $er->createQueryBuilder('b')
                    ->orderBy('b.id', 'ASC')
                    ->andWhere('b.type IN (:types)')
                    ->setParameter('types', $blockTypes);
            };
        });
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['block_preview'] = ''; // $options['block_attr'];

        /** @var ChoiceView $choice */
        foreach ($view->vars['choices'] as $choice) {
            if ($view->vars['value'] == $choice->value) {
                $view->vars['block_preview'] = $choice->attr['data-block-preview'];
            }
        }
    }
}
