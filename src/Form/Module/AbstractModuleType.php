<?php

namespace Softspring\CmsBundle\Form\Module;

use Softspring\CmsBundle\Form\Admin\SiteChoiceType;
use Softspring\CmsBundle\Helper\CmsHelper;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\Component\PolymorphicFormType\Form\Type\Node\AbstractNodeType;
use Symfony\Component\Form\Event\PreSetDataEvent;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractModuleType extends AbstractNodeType
{
    protected CmsHelper $cmsHelper;

    public function __construct(CmsHelper $cmsHelper)
    {
        $this->cmsHelper = $cmsHelper;
    }

    public function configureChildOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'module_id' => null,
            'module_revision' => null,
            'module_migrations' => [],
            'translation_domain' => 'sfs_cms_modules',
            'content_type' => null,
            'content' => null,
            'row_class' => '',
            'locale_filter' => true,
            'site_filter' => true,
            'deprecated' => false,
            'available_sites' => null,
            'available_locales' => null,
        ]);

        $resolver->setRequired('module_id');
        $resolver->setAllowedTypes('module_id', ['string']);

        $resolver->setRequired('module_revision');
        $resolver->setAllowedTypes('module_revision', ['int']);

        $resolver->setRequired('module_migrations');
        $resolver->setAllowedTypes('module_migrations', ['array']);

        $resolver->setRequired('content_type');
        $resolver->setAllowedTypes('content_type', ['string']);

        $resolver->setRequired('content');
        $resolver->setAllowedTypes('content', [ContentInterface::class]);

        $resolver->setDefault('form_template', null);
        $resolver->setAllowedTypes('form_template', ['null', 'string']);

        $resolver->setDefault('edit_template', null);
        $resolver->setAllowedTypes('edit_template', ['null', 'string']);

        $resolver->setAllowedTypes('deprecated', ['bool', 'string']);

        $resolver->setAllowedTypes('available_sites', ['null', 'array']);
        $resolver->setAllowedTypes('available_locales', ['null', 'array']);

        $resolver->setNormalizer('available_sites', function (Options $options, $value) {
            return $this->cmsHelper->site()->normalizeFormAvailableSites($value, $options['content']);
        });

        $resolver->setNormalizer('available_locales', function (Options $options, $value) {
            return $this->cmsHelper->locale()->normalizeFormAvailableLocalesForContent($value, $options['content']);
        });
    }

    public function buildChildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['module_id'] = $options['module_id'];
        $view->vars['form_template'] = $options['form_template'];
        $view->vars['edit_template'] = $options['edit_template'];
        $view->vars['sites'] = $options['available_sites'];
        $view->vars['locales'] = $options['available_locales'];
        $view->vars['attr']['class'] = (isset($view->vars['attr']['class']) ? $view->vars['attr']['class'].' ' : '').$options['row_class'];
        $view->vars['deprecated'] = $options['deprecated'];
        $view->vars['attr']['data-module-id'] = $options['module_id'];
    }

    protected function buildChildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options['locale_filter'] && sizeof($options['available_locales']) > 1) {
            $builder->add('locale_filter', ChoiceType::class, [
                'multiple' => true,
                'expanded' => true,
                'block_prefix' => 'module_locale_filter',
                'choice_translation_domain' => false,
                'choices' => array_combine($options['available_locales'], $options['available_locales']),
            ]);

            $builder->addEventListener(FormEvents::PRE_SET_DATA, function (PreSetDataEvent $event) {
                $data = $event->getData();
                $options = $event->getForm()->getConfig()->getOptions();

                if (null === $data) {
                    // set all locales on prototyping (data = null)
                    $data = ['locale_filter' => $options['available_locales']];
                }

                if (!isset($data['locale_filter'])) {
                    // set all locales on no stored locale_filter
                    $data['locale_filter'] = $options['available_locales'];
                }

                $event->setData($data);
            });
        }

        if ($options['site_filter'] && $options['content']->getSites()->count() > 1) {
            $builder->add('site_filter', SiteChoiceType::class, [
                'multiple' => true,
                'expanded' => true,
                'block_prefix' => 'module_site_filter',
                'choice_translation_domain' => false,
                'content' => $options['content'],
            ]);

            $builder->addEventListener(FormEvents::PRE_SET_DATA, function (PreSetDataEvent $event) {
                $data = $event->getData();
                $allAvailableSites = $event->getForm()->getConfig()->getOption('content')->getSites()->toArray();

                if (null === $data) {
                    // set all locales on prototyping (data = null)
                    $data = ['site_filter' => $allAvailableSites];
                }

                if (!isset($data['site_filter'])) {
                    // set all locales on no stored site_filter
                    $data['site_filter'] = $allAvailableSites;
                }

                $event->setData($data);
            });
        }

        $builder->add('_revision', HiddenType::class, [
            'data' => $options['module_revision'],
        ]);
    }
}
