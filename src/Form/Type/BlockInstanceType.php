<?php

namespace Softspring\CmsBundle\Form\Type;

use Closure;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Softspring\CmsBundle\Helper\CmsHelper;
use Softspring\CmsBundle\Model\BlockInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

class BlockInstanceType extends AbstractType
{
    public function __construct(
        protected EntityManagerInterface $em,
        protected CmsHelper $cmsHelper,
        protected RouterInterface $router,
    ) {
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
            'query_builder' => fn (EntityRepository $entityRepository): QueryBuilder => $entityRepository->createQueryBuilder('b'),
            'choice_label' => function (BlockInterface $block): ?string {
                return $block->getName();
            },
            'choice_filter' => function (?BlockInterface $block = null): bool {
                return $block instanceof BlockInterface ? $this->cmsHelper->config()->getBlock($block->getType())['enabled'] : false;
            },
            'choice_attr' => function (?BlockInterface $block): array {
                $attr = [
                    'data-block-preview' => '',
                ];

                if ($block instanceof BlockInterface) {
                    $blockConfig = $this->cmsHelper->config()->getBlock($block->getType());
                    $blockConfig['esi'] && $attr['data-block-esi'] = '';
                    $blockConfig['singleton'] && $attr['data-block-singleton'] = '';
                    $blockConfig['schedulable'] && $attr['data-block-schedulable'] = '';
                    $blockConfig['cache_ttl'] && $attr['data-block-cache-ttl'] = '';

                    foreach ($this->cmsHelper->config()->getSites() as $site) {
                        foreach ($this->cmsHelper->locale()->getEnabledLocales() as $locale) {
                            $attr['data-block-preview'] .= '<div data-lang="'.$locale.'" data-site="'.$site.'" class="section-preview"'
                                .' data-preview-url="'.$this->router->generate('sfs_cms_admin_blocks_render_preview_by_type', ['type' => $block->getType(), '_locale' => $locale, '_sfs_cms_site' => $site]).'"'
                                .'></div>';
                        }
                    }
                }

                return $attr;
            },
        ]);

        $resolver->addAllowedTypes('block_types', ['null', 'array', 'string']);
        $resolver->setNormalizer('block_types', function (Options $options, $value) {
            return is_string($value) ? [$value] : $value;
        });

        $resolver->setDefault('query_builder', function (Options $options): Closure {
            $blockTypes = $options['block_types'];

            if (null === $blockTypes) {
                $blockTypes = array_keys($this->cmsHelper->config()->getBlocks());
            }

            $blockTypes = array_filter($blockTypes, function (string $blockType): bool {
                $config = $this->cmsHelper->config()->getBlock($blockType);

                return $config && !$config['static'];
            });

            return function (EntityRepository $er) use ($blockTypes): QueryBuilder {
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
