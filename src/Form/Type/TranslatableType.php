<?php

namespace Softspring\CmsBundle\Form\Type;

use Softspring\CmsBundle\Form\DynamicFormTrait;
use Softspring\CmsBundle\Translator\TranslatableContext;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TranslatableType extends AbstractType
{
    use DynamicFormTrait;

    public function __construct(protected TranslatableContext $translatableContext)
    {
    }

    public function getBlockPrefix(): string
    {
        return 'translatable';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'required' => false,
            'default_language' => $this->translatableContext->getDefaultLocale(),
            'languages' => $this->translatableContext->getLocales(),
            'children_attr' => [],
            'type' => 'text',
            'type_options' => [],
        ]);

        $resolver->setRequired('languages');
        $resolver->setAllowedTypes('languages', 'array');
        $resolver->setRequired('default_language');
        $resolver->setAllowedTypes('default_language', 'string');
        $resolver->setRequired('type');
        $resolver->setAllowedTypes('type', 'string');
        $resolver->setAllowedTypes('type_options', 'array');
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('_trans_id', HiddenType::class);
        $builder->add('_default', HiddenType::class);

        foreach ($options['languages'] as $lang) {
            $childrenOptions = [
                'required' => $lang == $options['default_language'],
                'label' => $lang,
                'translation_domain' => false,
                'block_prefix' => 'translatable_element',
                'attr' => [],
            ];

            $childrenOptions = array_merge($childrenOptions, $options['type_options']);

            $childrenOptions['attr'] = array_merge($childrenOptions['attr'], $options['type_options']['attr'] ?? [], [
                'data-input-lang' => $lang,
            ]);

            foreach ($options['children_attr'] ?? [] as $attr => $value) {
                $childrenOptions['attr'][$attr] = $value;
            }

            $builder->add($lang, $this->getFieldType($options['type']), $childrenOptions);
        }

        $callback = function ($value) use ($options) { return $this->transform($value, $options); };
        $builder->addModelTransformer(new CallbackTransformer($callback, $callback));
    }

    protected function transform(?array $data, array $options): array
    {
        if (empty($data['_trans_id'])) {
            $data['_trans_id'] = uniqid();
        }

        if (empty($data['_default'])) {
            $data['_default'] = $options['default_language'];
        }

        return $data;
    }
}
