<?php

namespace Softspring\CmsBundle\Form\Admin\Content;

use Softspring\CmsBundle\Form\Type\DynamicFormType;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Translator\TranslatableContext;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Intl\Locales;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentUpdateForm extends AbstractType implements ContentUpdateFormInterface
{
    public function __construct(protected TranslatableContext $translatableContext)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ContentInterface::class,
            'validation_groups' => ['Default', 'update'],
            'translation_domain' => 'sfs_cms_contents',
        ]);

        $resolver->setRequired('content_config');

        $resolver->setNormalizer('label_format', function (Options $options, $value) {
            return "admin_{$options['content_config']['_id']}.form.%name%.label";
        });

        $resolver->setDefault('locales', $this->translatableContext->getEnabledLocales());
        $resolver->setRequired('locales');
        $resolver->setAllowedTypes('locales', ['array']);

        $resolver->setRequired('content');
        $resolver->setAllowedTypes('content', [ContentInterface::class]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('name', TextType::class);

        $content = $options['content'];
        $addLocales = array_diff($options['locales'], $content->getLocales());
        if (!empty($addLocales)) {
            $builder->add('addLocale', ChoiceType::class, [
                'expanded' => true,
                'multiple' => true,
                'choice_translation_domain' => false,
                'choices' => array_combine(array_map(fn ($lang) => Locales::getName($lang), $options['locales']), $options['locales']),
                'mapped' => false,
            ]);
        }

        if (!empty($options['content_config']['extra_fields'])) {
            $builder->add('extraData', DynamicFormType::class, [
                'form_fields' => $options['content_config']['extra_fields'],
                'translation_domain' => 'sfs_cms_contents',
            ]);
        }

        $builder->add('indexing', DynamicFormType::class, [
            'form_fields' => $options['content_config']['indexing'] ?? [],
            'translation_domain' => 'sfs_cms_contents',
            'label' => "admin_{$options['content_config']['_id']}.form.indexing.label",
            'label_format' => "admin_{$options['content_config']['_id']}.form.indexing.%name%.label",
        ]);
    }
}
