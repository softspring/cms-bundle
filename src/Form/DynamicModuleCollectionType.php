<?php

namespace Softspring\CmsBundle\Form;

use Softspring\PolymorphicFormType\Form\DataTransformer\NodeDataTransformer;
use Softspring\PolymorphicFormType\Form\Discriminator\NodeDiscriminator;
use Softspring\PolymorphicFormType\Form\EventListener\NodesResizeFormListener;
use Softspring\PolymorphicFormType\Form\Type\Node\AbstractNodeType;
use Softspring\PolymorphicFormType\Form\Type\PolymorphicCollectionType;
use Symfony\Component\Form\Exception\RuntimeException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DynamicModuleCollectionType extends PolymorphicCollectionType
{
    protected FormFactory $formFactory;
    protected array $dynamicModules;

    public function __construct(FormFactory $formFactory, array $dynamicModules)
    {
        parent::__construct($formFactory);
        $this->dynamicModules = $dynamicModules;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $typesMap = [];
        $typesOptions = [];
        $discriminatorMap = [];
        foreach ($this->dynamicModules as $dynamicModule => $config) {
            $typesMap[$dynamicModule] = DynamicFormType::class;

            $typesOptions[$dynamicModule]['form_template'] = $config['form_template'] ?? null;
            $typesOptions[$dynamicModule]['edit_template'] = $config['edit_template'] ?? null;
            $typesOptions[$dynamicModule]['form_fields'] = $config['form_fields'];
            $typesOptions[$dynamicModule]['prototype_button_label'] = $dynamicModule;
            $typesOptions[$dynamicModule]['label'] = "Módulo $dynamicModule";
            $discriminatorMap[$dynamicModule] = 'array';
        }

        $resolver->setDefaults([
            'translation_domain' => 'sfs_cms',
            'error_bubbling' => false,
            'entity_manager' => null,
            'types_map' => $typesMap,
            'types_options' => $typesOptions,
            'discriminator_map' => $discriminatorMap,
            'discriminator_field' => '_type',
            'allowed_modules' => [],
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if (!$options['allow_add'] || !$options['prototype']) {
            return;
        }

        $prototypes = [];

        if (!empty($options['allowed_modules'])) {
            $modules = array_keys($options['types_map']);
            $disallowedModules = array_diff($modules, $options['allowed_modules']);

            foreach ($disallowedModules as $disallowedModule) {
                unset($options['types_map'][$disallowedModule]);
            }
        }

        foreach ($options['types_map'] as $discr => $formClass) {
            /* @var AbstractNodeType $formType */
            if (is_object($formClass)) {
                $formType = $formClass;
            } elseif (class_exists($formClass)) {
                $formType = $formClass;
            } else {
                $formType = $formClass;
            }

            $formOptions = $options['types_options'][$discr] ?? [];
            $formOptions['discriminator_field'] = $options['discriminator_field'];
            $formOptions['id_field'] = $options['id_field'];

            $prototypeForm = $this->getFormFactory($options)->createNamedBuilder($options['prototype_name'], $formType, null, $formOptions)->getForm();
            $prototypeForm->get($options['discriminator_field'])->setData($discr);
            $prototypes[$discr] = $prototypeForm->createView($view);
        }

        $view->vars['prototypes'] = $prototypes;
    }

    /**
     * Configure event subscriber for resizing with data transformer.
     */
    protected function configureResizeEventSubscriber(FormBuilderInterface $builder, array $options)
    {
        if (empty($options['discriminator_map'])) {
            throw new RuntimeException('discriminator_map must be set');
        }

        if (!empty($options['allowed_modules'])) {
            $modules = array_unique(array_merge(array_keys($options['types_map']), array_keys($options['types_options']), array_keys($options['discriminator_map'])));
            $disallowedModules = array_diff($modules, $options['allowed_modules']);

            foreach ($disallowedModules as $disallowedModule) {
                unset($options['types_map'][$disallowedModule]);
                unset($options['discriminator_map'][$disallowedModule]);
                unset($options['types_options'][$disallowedModule]);
            }
        }

        $discriminator = new NodeDiscriminator($options['discriminator_map'], $options['types_map'], $options['types_options'], $options['discriminator_field']);
        $transformer = new NodeDataTransformer($discriminator, $options['discriminator_field'], $options['id_field']);
        $builder->addEventSubscriber(new NodesResizeFormListener($discriminator, $transformer, $options['discriminator_field'], $options['id_field']));
    }
}