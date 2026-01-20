<?php

namespace Softspring\CmsBundle\Form\Type;

use Softspring\CmsBundle\Form\DynamicFormTrait;
use Softspring\CmsBundle\Translator\TranslatableContext;
use Softspring\Component\DynamicFormType\Form\Resolver\TypeResolverInterface;
use Softspring\TranslatableBundle\Form\Type\TranslationType as BaseTranslationType;
use Softspring\TranslatableBundle\Model\Translation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TranslationType extends AbstractType
{
    use DynamicFormTrait;

    public function __construct(protected TranslatableContext $translatableContext, protected ?TypeResolverInterface $typeResolver = null)
    {
    }

    public function getParent(): string
    {
        return BaseTranslationType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'cms_translation';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'default_language' => $this->translatableContext->getDefaultLocale(),
            'languages' => $this->translatableContext->getLocales(),
            'type' => TextType::class,
        ]);

        $resolver->setNormalizer('type', function ($options, $value) {
            return $this->getFieldType($value);
        });
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('_json', HiddenType::class);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $data = $event->getData();
            if ($data instanceof Translation) {
                unset($data['_json']);
                $data->setTranslation('_json', json_encode($data->__toArray()));
            }
        });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();

            $json = json_decode($data['_json'], true);
            unset($json['_json']);
            $data = array_merge($data, $json);
            $event->setData($data);
        });
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        $translationJsonFieldId = $view->children['_json']->vars['id'];

        foreach ($view->children as $locale => $field) {
            if ('_json' !== $locale) {
                $field->vars['full_name'] = '';

                if (!str_starts_with($locale, '_')) {
                    $field->vars['attr']['data-translation-json-field'] = $translationJsonFieldId;
                }
            }
        }
    }
}
