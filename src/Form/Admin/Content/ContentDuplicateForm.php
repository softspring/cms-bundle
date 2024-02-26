<?php

namespace Softspring\CmsBundle\Form\Admin\Content;

use Doctrine\ORM\EntityManagerInterface;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\Translator\TranslatableContext;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentDuplicateForm extends ContentCreateForm
{
    public function __construct(TranslatableContext $translatableContext, protected EntityManagerInterface $em)
    {
        parent::__construct($translatableContext);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setRequired('origin_content');
        $resolver->setAllowedTypes('origin_content', [ContentInterface::class]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder->add('duplicateVersion', EntityType::class, [
            'class' => ContentVersionInterface::class,
            'choice_label' => function (ContentVersionInterface $version) {
                return 'v'.$version->getVersionNumber().($version->isPublished() ? ' (published)' : '');
            },
            'choices' => $options['origin_content']->getVersions(),
            'required' => true,
            'mapped' => false,
            'em' => $this->em,
            'default_value' => $options['origin_content']->getPublishedVersion(),
        ]);
    }
}
