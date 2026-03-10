<?php

namespace Softspring\CmsBundle\Form\Admin\Content;

use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\VersionInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ContentDiffForm extends AbstractType implements ContentDiffFormInterface
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'validation_groups' => ['Default', 'import'],
            'translation_domain' => 'sfs_cms_contents',
            'content_config' => null,
            'method' => 'GET',
        ]);

        $resolver->setRequired('content');
        $resolver->setAllowedTypes('content', [ContentInterface::class]);

        $resolver->setNormalizer('label_format', function (Options $options, $value): string {
            return "admin_{$options['content_config']['_id']}.diff.form.%name%.label";
        });
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var ContentInterface $content */
        $content = $options['content'];

        $versionFieldsOptions = [
            'choices' => $content->getVersions(),
            'choice_label' => function (VersionInterface $version): string {
                return 'v'.$version->getVersionNumber();
            },
            'choice_value' => function (?VersionInterface $version) {
                return $version?->getId();
            },
            'required' => false,
            'constraints' => [new NotBlank()],
        ];

        $builder->add('version1', ChoiceType::class, $versionFieldsOptions);
        $builder->add('version2', ChoiceType::class, $versionFieldsOptions);
    }
}
