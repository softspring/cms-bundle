<?php

namespace Softspring\CmsBundle\Form\Admin\ContentVersion;

use Doctrine\ORM\EntityManagerInterface;
use Softspring\CmsBundle\Config\Exception\InvalidContentException;
use Softspring\CmsBundle\Form\Admin\LayoutContentType;
use Softspring\CmsBundle\Form\Module\ModuleCollectionType;
use Softspring\CmsBundle\Form\Type\LayoutType;
use Softspring\CmsBundle\Helper\CmsHelper;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotEqualTo;

class VersionCreateForm extends AbstractType implements VersionCreateFormInterface
{
    public function __construct(protected EntityManagerInterface $em, protected CmsHelper $cmsHelper)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ContentVersionInterface::class,
            'label_format' => 'admin_content.form.%name%.label',
            'validation_groups' => ['Default', 'create'],
            'translation_domain' => 'sfs_cms_contents',
            'layout' => null,
            'content_type' => null,
            'content_config' => null,
        ]);

        //        $resolver->setNormalizer('label_format', function (Options $options, $value) {
        //            return "admin_{$options['content_config']['_id']}.form.%name%.label";
        //        });

        $resolver->setRequired('content_type');
        $resolver->setAllowedTypes('content_type', ['string']);

        $resolver->setRequired('content');
        $resolver->setAllowedTypes('content', [ContentInterface::class]);

        $resolver->setNormalizer('label_format', function (Options $options, $value) {
            return "admin_{$options['content_type']}.form.%name%.label";
        });
    }

    /**
     * @throws InvalidContentException
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('layout', LayoutType::class, [
            'choices' => $this->cmsHelper->layout()->getAvailableLayouts($options['content']),
        ]);

        $builder->add('data', LayoutContentType::class, [
            'layout' => $options['layout'],
            'content_type' => $options['content_type'],
            'content' => $options['content'],
        ]);
        $builder->add('_ok', HiddenType::class, [
            'mapped' => false,
            'constraints' => new NotEqualTo(1),
        ]);

        $builder->add('module_prototypes_collection', ModuleCollectionType::class, [
            'label' => '__label__',
            'block_prefix' => 'module_prototypes_collection',
            'translation_domain' => 'sfs_cms_layouts',
            'content_type' => $options['content_type'],
            'allowed_modules' => null,
            // random prototype name to allow multiple levels
            'prototype_name' => '___MODULE___',
            'module_collection_class' => 'container-fluid',
            'module_row_class' => 'row',
            'prototype' => true,
            'mapped' => false,
            'content' => $options['content'],
        ]);
    }
}
