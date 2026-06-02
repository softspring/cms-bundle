<?php

namespace Softspring\CmsBundle\Form\Admin\Content;

use Softspring\CmsBundle\Form\Admin\SiteChoiceType;
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
use Symfony\Component\Validator\Constraints\Count;

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

        $resolver->setNormalizer('label_format', function (Options $options, $value): string {
            return "admin_{$options['content_config']['_id']}.form.%name%.label";
        });

        $resolver->setDefault('default_locale', $this->translatableContext->getDefaultLocale());
        $resolver->setRequired('default_locale');
        $resolver->setAllowedTypes('default_locale', ['string']);

        $resolver->setDefault('locales', $this->translatableContext->getEnabledLocales());
        $resolver->setRequired('locales');
        $resolver->setAllowedTypes('locales', ['array']);

        $resolver->setRequired('content');
        $resolver->setAllowedTypes('content', [ContentInterface::class]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $indexingFields = $this->indexingFields($options);

        $builder->add('name', TextType::class);

        $builder->add('defaultLocale', ChoiceType::class, [
            'choice_translation_domain' => false,
            'choices' => array_combine(array_map(fn (string $lang): string => Locales::getName($lang), $options['locales']), $options['locales']),
            'default_value' => $options['default_locale'],
        ]);

        $builder->add('locales', ChoiceType::class, [
            'multiple' => true,
            'expanded' => true,
            'choice_translation_domain' => false,
            'choices' => array_combine(array_map(fn (string $lang): string => Locales::getName($lang), $options['locales']), $options['locales']),
            'constraints' => new Count(min: 1),
            'default_value' => [$options['default_locale']],
        ]);

        $builder->add('sites', SiteChoiceType::class, [
            'content' => $options['content_config'],
            'by_reference' => false,
            'constraints' => new Count(min: 1),
        ]);

        if (!empty($options['content_config']['extra_fields'])) {
            $builder->add('extraData', DynamicFormType::class, [
                'form_fields' => $options['content_config']['extra_fields'],
                'translation_domain' => 'sfs_cms_contents',
            ]);
        }

        $builder->add('indexing', DynamicFormType::class, [
            'form_fields' => $indexingFields,
            'translation_domain' => 'sfs_cms_contents',
            'label' => "admin_{$options['content_config']['_id']}.form.indexing.label",
            'label_format' => "admin_{$options['content_config']['_id']}.form.indexing.%name%.label",
        ]);
    }

    protected function indexingFields(array $options): array
    {
        $indexingFields = $options['content_config']['indexing'] ?? [];
        $contentType = $options['content_config']['_id'];

        if (isset($indexingFields['sitemapChangefreq']['type_options']['choices'])) {
            $indexingFields['sitemapChangefreq']['type_options']['choices'] = array_combine(
                array_map(
                    fn (string $label): string => str_starts_with($label, 'admin_page.form.indexing.')
                        ? "admin_{$contentType}.".substr($label, strlen('admin_page.'))
                        : $label,
                    array_keys($indexingFields['sitemapChangefreq']['type_options']['choices'])
                ),
                array_values($indexingFields['sitemapChangefreq']['type_options']['choices'])
            );
        }

        return $indexingFields;
    }
}
