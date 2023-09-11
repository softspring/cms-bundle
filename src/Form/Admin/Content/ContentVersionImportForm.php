<?php

namespace Softspring\CmsBundle\Form\Admin\Content;

use Softspring\CmsBundle\Validator\ContentZipFile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ContentVersionImportForm extends AbstractType implements ContentVersionImportFormInterface
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'validation_groups' => ['Default', 'import_version'],
            'translation_domain' => 'sfs_cms_contents',
            'content' => null,
        ]);

        $resolver->setNormalizer('label_format', function (Options $options, $value) {
            return "admin_{$options['content']['_id']}.import_version.form.%name%.label";
        });
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('file', FileType::class, [
            'constraints' => [
                new NotBlank(),
                new ContentZipFile(),
            ],
        ]);
    }
}
