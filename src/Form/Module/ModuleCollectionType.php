<?php

namespace Softspring\CmsBundle\Form\Module;

use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Form\Traits\DataMapperTrait;
use Softspring\Component\PolymorphicFormType\Form\DataTransformer\NodeDataTransformer;
use Softspring\Component\PolymorphicFormType\Form\Discriminator\NodeDiscriminator;
use Softspring\Component\PolymorphicFormType\Form\EventListener\NodesResizeFormListener;
use Softspring\Component\PolymorphicFormType\Form\Type\PolymorphicCollectionType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\InvalidConfigurationException;
use Symfony\Component\Form\Exception\RuntimeException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ModuleCollectionType extends PolymorphicCollectionType implements DataMapperInterface
{
    use DataMapperTrait;

    protected FormFactory $formFactory;
    protected CmsConfig $cmsConfig;

    public function __construct(FormFactory $formFactory, CmsConfig $cmsConfig)
    {
        parent::__construct($formFactory);
        $this->cmsConfig = $cmsConfig;
    }

    public function getBlockPrefix(): string
    {
        return 'module_collection';
    }

    public function configureModulesDiscriminatorMap(): array
    {
        $discriminatorMap = [];

        foreach ($this->cmsConfig->getModules() as $moduleId => $config) {
            $discriminatorMap[$moduleId] = 'array';
        }

        return $discriminatorMap;
    }

    public function configureModulesTypesOptions(): array
    {
        $typesOptions = [];

        foreach ($this->cmsConfig->getModules() as $moduleId => $config) {
            $typesOptions[$moduleId] = $config['module_options'] ?? [];
            $typesOptions[$moduleId]['compatible_contents'] = $config['compatible_contents'] ?? [];
            $typesOptions[$moduleId]['form_template'] = $config['form_template'] ?? null;
            $typesOptions[$moduleId]['edit_template'] = $config['edit_template'] ?? null;
            $typesOptions[$moduleId]['prototype_button_label'] = "$moduleId.prototype_button";
            $typesOptions[$moduleId]['label'] = "$moduleId.label";
            $typesOptions[$moduleId]['label_format'] = "$moduleId.form.%name%.label";
            $typesOptions[$moduleId]['translation_domain'] = 'sfs_cms_modules';
            $typesOptions[$moduleId]['module_id'] = $moduleId;
        }

        return $typesOptions;
    }

    public function configureModulesTypesMap(): array
    {
        $typesMap = [];

        foreach ($this->cmsConfig->getModules() as $moduleId => $config) {
            $typeReflection = new \ReflectionClass($config['module_type']);
            if (!$typeReflection->isSubclassOf(AbstractModuleType::class)) {
                throw new InvalidConfigurationException(sprintf('%s class configured in module\'s module_type option must extends %s', $config['form_type'], AbstractNodeType::class));
            }

            $typesMap[$moduleId] = $config['module_type']; // default is Softspring\CmsBundle\Form\Module\DynamicFormModuleType
        }

        return $typesMap;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'translation_domain' => 'sfs_cms_modules',
            'error_bubbling' => false,
            'entity_manager' => null,
            'types_map' => $this->configureModulesTypesMap(),
            'types_options' => $this->configureModulesTypesOptions(),
            'discriminator_map' => $this->configureModulesDiscriminatorMap(),
            'discriminator_field' => '_module',
            'allowed_modules' => null,
            'allowed_container_modules' => null,
            'compatible_contents' => [],
            'content_type' => null,
            'module_collection_class' => '',
            'module_row_class' => '',
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $options = $this->removeInvalidModulesForContentType($options);
        parent::buildForm($builder, $options);
        $builder->setDataMapper($this);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if (!$options['allow_add'] || !$options['prototype']) {
            return;
        }

        $options = $this->filterAllowedModules($options);
        $options = $this->removeInvalidModulesForContentType($options);

        // propagate container fields
        foreach ($options['types_map'] as $type => $typeClass) {
            $options['types_options'][$type]['content_type'] = $options['content_type'];
            $options['types_options'][$type]['row_class'] = $options['module_row_class'];

            if ($typeClass == ContainerModuleType::class) {
                $options['types_options'][$type]['compatible_contents'] = $options['compatible_contents'];
            }
        }

        $view->vars['module_collection_class'] = $options['module_collection_class'];

        parent::buildView($view, $form, $options);
    }

    /**
     * Configure event subscriber for resizing with data transformer.
     */
    protected function configureResizeEventSubscriber(FormBuilderInterface $builder, array $options)
    {
        if (empty($options['discriminator_map'])) {
            throw new RuntimeException('discriminator_map must be set');
        }

        $options = $this->filterAllowedModules($options);

        // propagate container fields
        foreach ($options['types_map'] as $type => $typeClass) {
            $options['types_options'][$type]['content_type'] = $options['content_type'];
            $options['types_options'][$type]['row_class'] = $options['module_row_class'];

            if ($typeClass == ContainerModuleType::class) {
                $options['types_options'][$type]['compatible_contents'] = $options['compatible_contents'];
            }
        }

        $discriminator = new NodeDiscriminator($options['discriminator_map'], $options['types_map'], $options['types_options'], $options['discriminator_field']);
        $transformer = new NodeDataTransformer($discriminator, $options['discriminator_field'], $options['id_field']);
        $builder->addEventSubscriber(new NodesResizeFormListener($discriminator, $transformer, $options['discriminator_field'], $options['id_field']));
    }

    protected function removeInvalidModulesForContentType(array $options): array
    {
        foreach ($options['types_options'] as $type => $typeOptions) {
            if (!empty($typeOptions['compatible_contents']) && !in_array($options['content_type'], $typeOptions['compatible_contents'])) {
                unset($options['types_options'][$type]);
                unset($options['types_map'][$type]);
                unset($options['discriminator_map'][$type]);
            }
            unset($options['types_options'][$type]['compatible_contents']);
        }

        return $options;
    }

    protected function filterAllowedModules(array $options): array
    {
        if (is_array($options['allowed_modules'])) {
            $modules = array_keys($options['types_map']);
            $disallowedModules = array_diff($modules, $options['allowed_modules']);

            foreach ($disallowedModules as $disallowedModule) {
                unset($options['types_map'][$disallowedModule]);
                unset($options['discriminator_map'][$disallowedModule]);
                unset($options['types_options'][$disallowedModule]);
            }
        }

        if (!is_null($options['allowed_container_modules'])) {
            $containerModules = array_keys(array_filter($options['types_map'], fn ($type) => $type === ContainerModuleType::class));
            $disallowedModules = array_diff($containerModules, $options['allowed_container_modules']);

            foreach ($disallowedModules as $disallowedModule) {
                unset($options['types_map'][$disallowedModule]);
                unset($options['discriminator_map'][$disallowedModule]);
                unset($options['types_options'][$disallowedModule]);
            }
        }

        return $options;
    }
}