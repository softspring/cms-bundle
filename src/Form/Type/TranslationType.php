<?php

namespace Softspring\CmsBundle\Form\Type;

use Softspring\CmsBundle\Form\DynamicFormTrait;
use Softspring\CmsBundle\Translator\TranslatableContext;
use Softspring\Component\DynamicFormType\Form\Resolver\TypeResolverInterface;
use Softspring\TranslatableBundle\Form\Type\TranslationType as BaseTranslationType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
        //        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'default_language' => $this->translatableContext->getDefaultLocale(),
            'languages' => $this->translatableContext->getLocales(),
            'type' => TextType::class,
        ]);

        $resolver->setNormalizer('type', function ($options, $value) {
            return $this->getFieldType($value);
        });
    }
}
