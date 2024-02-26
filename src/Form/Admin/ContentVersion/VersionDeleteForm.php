<?php

namespace Softspring\CmsBundle\Form\Admin\ContentVersion;

use Doctrine\ORM\EntityManagerInterface;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VersionDeleteForm extends AbstractType
{
    public function __construct(protected EntityManagerInterface $em)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ContentVersionInterface::class,
            'validation_groups' => ['Default', 'delete'],
            'translation_domain' => 'sfs_cms_contents',
            'content_config' => null,
        ]);

        $resolver->setRequired('content_config');

        $resolver->setRequired('content');
        $resolver->setAllowedTypes('content', [ContentInterface::class]);

        $resolver->setNormalizer('label_format', function (Options $options, $value) {
            return "admin_{$options['content_config']['_id']}.form.%name%.label";
        });
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
    }
}
