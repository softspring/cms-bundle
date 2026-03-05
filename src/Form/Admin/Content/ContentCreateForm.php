<?php

namespace Softspring\CmsBundle\Form\Admin\Content;

use Doctrine\ORM\EntityRepository;
use Softspring\CmsBundle\Form\Admin\Route\RouteCollectionType;
use Softspring\CmsBundle\Form\Admin\SiteChoiceType;
use Softspring\CmsBundle\Form\Type\DynamicFormType;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Translator\TranslatableContext;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Intl\Locales;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentCreateForm extends AbstractType implements ContentCreateFormInterface
{
    public function __construct(protected TranslatableContext $translatableContext)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ContentInterface::class,
            'validation_groups' => ['Default', 'create'],
            'translation_domain' => 'sfs_cms_contents',
        ]);

        $resolver->setRequired('content_config');

        $resolver->setNormalizer('label_format', function (Options $options, $value) {
            return "admin_{$options['content_config']['_id']}.form.%name%.label";
        });

        $resolver->setDefault('default_locale', $this->translatableContext->getDefaultLocale());
        $resolver->setRequired('default_locale');
        $resolver->setAllowedTypes('default_locale', ['string']);

        $resolver->setDefault('locales', $this->translatableContext->getEnabledLocales());
        $resolver->setRequired('locales');
        $resolver->setAllowedTypes('locales', ['array']);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('name', TextType::class, [
            'attr' => [
                'data-generate-underscore' => 'data-route-id',
                'data-generate-slug' => 'data-route-path',
            ],
        ]);

        $builder->add('defaultLocale', ChoiceType::class, [
            'choice_translation_domain' => false,
            'choices' => array_combine(array_map(fn ($lang) => Locales::getName($lang), $options['locales']), $options['locales']),
            'default_value' => $options['default_locale'],
        ]);

        $builder->add('locales', ChoiceType::class, [
            'multiple' => true,
            'expanded' => true,
            'choice_translation_domain' => false,
            'choices' => array_combine(array_map(fn ($lang) => Locales::getName($lang), $options['locales']), $options['locales']),
            'default_value' => [$options['default_locale']],
        ]);

        $builder->add('sites', SiteChoiceType::class, [
            'content' => $options['content_config'],
            'by_reference' => false,
        ]);

        $builder->add('canonicalPage', EntityType::class, [
            'class' => $options['content_config']['entity_class'],
            'required' => false,
            'choice_label' => 'name',
            'placeholder' => "admin_{$options['content_config']['_id']}.form.canonicalPage.placeholder",
            'help' => "admin_{$options['content_config']['_id']}.form.canonicalPage.help",
            'query_builder' => fn (EntityRepository $repository) => $repository->createQueryBuilder('c')->orderBy('c.name', 'ASC'),
        ]);

        if (!empty($options['content_config']['extra_fields'])) {
            $builder->add('extraData', DynamicFormType::class, [
                'form_fields' => $options['content_config']['extra_fields'],
                'translation_domain' => 'sfs_cms_contents',
            ]);
        }

        $builder->add('routes', RouteCollectionType::class, [
            'allow_add' => false,
            'allow_delete' => false,
            'entry_options' => [
                'content_relative' => true,
                'translation_domain' => 'sfs_cms_contents',
                'label_format' => "admin_{$options['content_config']['_id']}.form.routes.%name%.label",
            ],
        ]);

        $builder->add('indexing', DynamicFormType::class, [
            'form_fields' => $options['content_config']['indexing'] ?? [],
            'translation_domain' => 'sfs_cms_contents',
            'label' => "admin_{$options['content_config']['_id']}.form.indexing.label",
            'label_format' => "admin_{$options['content_config']['_id']}.form.indexing.%name%.label",
        ]);
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        array_map(function (FormView $localeInput) use ($view) {
            $localeInput->vars['attr']['data-locale-add-path'] = $view->children['routes']->children[0]->children['paths']->vars['id'] ?? '';
        }, $view->children['locales']->children);
    }
}
