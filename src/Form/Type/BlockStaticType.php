<?php

namespace Softspring\CmsBundle\Form\Type;

use Softspring\CmsBundle\Helper\CmsHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

class BlockStaticType extends AbstractType
{
    public function __construct(
        protected CmsHelper $cmsHelper,
        protected RouterInterface $router,
    ) {
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
            'choice_label' => function (?object $blockConfig): string {
                return $blockConfig->_id ? "admin_{$blockConfig->_id}.name" : '';
            },
            'choice_filter' => function (?object $blockConfig): bool {
                return $blockConfig && $blockConfig->static && $blockConfig->enabled;
            },
            'choice_attr' => function (?object $blockConfig): array {
                $attr = [
                    'data-block-preview' => '',
                ];

                if (!$blockConfig) {
                    return $attr;
                }

                $blockConfig->esi && $attr['data-block-esi'] = '';
                $blockConfig->singleton && $attr['data-block-singleton'] = '';
                $blockConfig->schedulable && $attr['data-block-schedulable'] = '';
                $blockConfig->cache_ttl && $attr['data-block-cache-ttl'] = '';

                foreach ($this->cmsHelper->config()->getSites() as $site) {
                    foreach ($this->cmsHelper->locale()->getEnabledLocales() as $locale) {
                        $attr['data-block-preview'] .= '<div data-lang="'.$locale.'" data-site="'.$site->getId().'" class="section-preview"'
                            .' data-preview-url="'.$this->router->generate('sfs_cms_admin_blocks_render_preview_by_type', ['type' => $blockConfig->_id, '_locale' => $locale, '_sfs_cms_site' => $site->getId()]).'"'
                            .'>BLOCK PREVIEW</div>';
                    }
                }

                return $attr;
            },
        ]);

        $resolver->addAllowedTypes('block_types', ['null', 'array', 'string']);
        $resolver->setNormalizer('block_types', function (Options $options, $value) {
            return is_string($value) ? [$value] : $value;
        });

        $resolver->setDefault('choices', function (Options $options): array {
            $blockTypes = $this->cmsHelper->config()->getBlocks();

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
                    $filtered = array_filter($options['choices'], fn ($choice): bool => $choice->_id == $blockAsKey);
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
