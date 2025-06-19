<?php

namespace Softspring\CmsBundle\Form\Admin\Section;

use Softspring\CmsBundle\Model\SectionInterface;
use Softspring\CmsBundle\Translator\TranslatableContext;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SectionUpdateForm extends AbstractType implements SectionUpdateFormInterface
{
    public function __construct(protected TranslatableContext $translatableContext)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SectionInterface::class,
            'validation_groups' => ['Default', 'update'],
            'translation_domain' => 'sfs_cms_admin',
            'label_format' => 'admin_sections.form.%name%.label',
        ]);

        //        $resolver->setDefault('default_locale', $this->translatableContext->getDefaultLocale());
        //        $resolver->setRequired('default_locale');
        //        $resolver->setAllowedTypes('default_locale', ['string']);
        //
        //        $resolver->setDefault('locales', $this->translatableContext->getEnabledLocales());
        //        $resolver->setRequired('locales');
        //        $resolver->setAllowedTypes('locales', ['array']);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('name', TextType::class, [
            'attr' => [
                'data-generate-underscore' => 'data-route-id',
                'data-generate-slug' => 'data-route-path',
            ],
        ]);
    }
}
