<?php

namespace Softspring\CmsBundle\Form\Type;

use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Render\BlockRenderer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BlockStaticType extends AbstractType
{
    protected CmsConfig $cmsConfig;
    protected BlockRenderer $blockRenderer;

    public function __construct(CmsConfig $cmsConfig, BlockRenderer $blockRenderer)
    {
        $this->cmsConfig = $cmsConfig;
        $this->blockRenderer = $blockRenderer;
    }

    public function getBlockPrefix(): string
    {
        return 'block_static';
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'required' => false,
            'block_types' => null,
            'choice_value' => '_id',
            'choice_translation_domain' => 'sfs_cms_blocks',
            'choice_label' => function (?object $blockConfig) {
                return $blockConfig->_id ? "{$blockConfig->_id}.name" : '';
            },
            'choice_filter' => function (?object $blockConfig) {
                return $blockConfig && $blockConfig->static;
            },
            'choice_attr' => function (?object $blockConfig) {
                $attr = [
                    'data-block-preview' => $blockConfig ? $this->blockRenderer->renderBlockByType($blockConfig->_id) : '',
                ];

                $blockConfig->esi && $attr['data-block-esi'] = '';
                $blockConfig->singleton && $attr['data-block-singleton'] = '';
                $blockConfig->schedulable && $attr['data-block-schedulable'] = '';
                $blockConfig->cache_ttl && $attr['data-block-cache-ttl'] = '';

                return $attr;
            },
            'choice_translation_domain' => false,
        ]);

        $resolver->addAllowedTypes('block_types', ['null', 'array', 'string']);
        $resolver->setNormalizer('block_types', function (Options $options, $value) {
            return is_string($value) ? [$value] : $value;
        });

        $resolver->setDefault('choices', function (Options $options) {
            $blockTypes = $this->cmsConfig->getBlocks();

            if (null !== $options['block_types']) {
                $blockTypes = array_intersect_key($blockTypes, array_flip($options['block_types']));
            }

            foreach ($blockTypes as $key => $value) {
                $blockTypes[$key] = (object) $value;
            }

            return array_values($blockTypes);
        });
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new CallbackTransformer(
            function ($blockAsKey) use ($options): ?object {
                if ($blockAsKey) {
                    $filtered = array_filter($options['choices'], fn ($choice) => $choice->_id == $blockAsKey);
                    /** @var false|object $choice */
                    $choice = current($filtered);

                    return false !== $choice ? $choice : null;
                }

                return null;
            },
            function (?object $blockAsObject): ?string {
                return $blockAsObject ? "{$blockAsObject->_id}" : null;
            }
        ));
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
