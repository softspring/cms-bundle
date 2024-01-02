<?php

namespace Softspring\CmsBundle\Form\Admin\ContentVersion;

use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Config\Exception\DisabledModuleException;
use Softspring\CmsBundle\Config\Exception\InvalidModuleException;
use Softspring\CmsBundle\Form\Type\TranslatableType;
use Softspring\CmsBundle\Model\ContentInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VersionTranslateForm extends AbstractType
{
    public function __construct(
        protected CmsConfig $cmsConfig
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'content_type' => null,
            'content_config' => null,
            'flatten_translations' => null,
        ]);

        $resolver->setRequired('content_type');
        $resolver->setAllowedTypes('content_type', ['string']);

        $resolver->setRequired('content');
        $resolver->setAllowedTypes('content', [ContentInterface::class]);

        $resolver->setRequired('flatten_translations');
        $resolver->setAllowedTypes('flatten_translations', ['array']);
    }

    /**
     * @throws InvalidModuleException
     * @throws DisabledModuleException
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $flattenTranslations = $options['flatten_translations'];

        foreach ($flattenTranslations as $field => $value) {
            if (str_ends_with($field, '_module')) {
                continue;
            }

            $module = substr($field, 0, strrpos($field, ':'));
            $moduleType = $flattenTranslations["$module:_module"];
            $fieldName = substr($field, strrpos($field, ':') + 1);
            $moduleConfig = $this->cmsConfig->getModule($moduleType);
            $fieldOptions = $moduleConfig['module_options']['form_fields'][$fieldName]['type_options'] ?? [];

            $builder->add($field, TranslatableType::class, [
                'translation_domain' => false,
                'type' => $fieldOptions['type'] ?? 'text',
                'attr' => [
                    'data-module' => $module,
                    'data-module-type' => $moduleType,
                    'data-module-field' => $fieldName,
                ],
                'children_attr' => [
                    'style' => in_array($fieldOptions['type']??'', ['textarea','html','wysiwyg']) ? 'height:300px' : '',
                ]
            ]);
        }
    }
}
