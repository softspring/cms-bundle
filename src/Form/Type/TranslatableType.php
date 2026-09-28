<?php

namespace Softspring\CmsBundle\Form\Type;

use Softspring\CmsBundle\Translator\TranslatableContext;
use Softspring\Component\DynamicFormType\Form\Resolver\TypeResolverInterface;
use Softspring\TranslatableBundle\Form\Type\TranslatableType as BaseTranslatableType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TranslatableType extends AbstractType
{
    public function __construct(protected TranslatableContext $translatableContext, protected ?TypeResolverInterface $typeResolver = null)
    {
    }

    public function getParent(): string
    {
        return BaseTranslatableType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'cms_translatable';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'default_language' => $this->translatableContext->getDefaultLocale(),
            'languages' => $this->translatableContext->getLocales(),
            'type' => TextType::class,
        ]);

        $resolver->setNormalizer('type', function ($options, string $value): string {
            return $this->typeResolver->resolveTypeClass($value);
        });
    }
}
