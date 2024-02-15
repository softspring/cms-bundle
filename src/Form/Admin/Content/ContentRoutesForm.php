<?php

namespace Softspring\CmsBundle\Form\Admin\Content;

use Softspring\CmsBundle\Form\Admin\Route\RouteCollectionType;
use Softspring\CmsBundle\Form\Admin\Route\RouteUpdateForm;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Translator\TranslatableContext;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentRoutesForm extends AbstractType
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
        $builder->add('routes', RouteCollectionType::class, [
            'allow_add' => false, // todo enable true
            'allow_delete' => false, // todo enable true
            'entry_type' => RouteUpdateForm::class,
            'entry_options' => [
                'content_relative' => true,
                'translation_domain' => 'sfs_cms_contents',
                'label_format' => "admin_{$options['content_config']['_id']}.form.routes.%name%.label",
            ],
        ]);
    }
}
