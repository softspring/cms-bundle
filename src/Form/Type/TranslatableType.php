<?php

namespace Softspring\CmsBundle\Form\Type;

use Softspring\CmsBundle\Form\DynamicFormTrait;
use Softspring\CmsBundle\Translator\TranslatableContext;
use Softspring\TranslatableBundle\Form\Type\TranslatableType as BaseTranslatableType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TranslatableType extends BaseTranslatableType
{
    use DynamicFormTrait;

    public function __construct(protected TranslatableContext $translatableContext)
    {
        parent::__construct(null, null);
    }

    public function getBlockPrefix(): string
    {
        return 'translatable';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'default_language' => $this->translatableContext->getDefaultLocale(),
            'languages' => $this->translatableContext->getLocales(),
        ]);

        $resolver->setNormalizer('type', function ($options, $value) {
            return $this->getFieldType($value);
        });
    }
}
