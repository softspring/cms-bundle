<?php

namespace Softspring\CmsBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Event\PreSetDataEvent;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LocaleFilterType extends AbstractType
{
    public function getParent(): string
    {
        return ChoiceType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'multiple' => true,
            'expanded' => true,
            'block_prefix' => 'module_locale_filter',
            'choice_translation_domain' => false,
            'available_locales' => null,
        ]);

        $resolver->setRequired('available_locales');
        $resolver->setAllowedTypes('available_locales', ['array']);

        $resolver->setNormalizer('choices', function (OptionsResolver $options, $value) {
            return empty($value) ? array_combine($options['available_locales'], $options['available_locales']) : $value;
        });
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $availableLocales = $options['available_locales'];

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (PreSetDataEvent $event) use ($availableLocales) {
            self::onPreSetData($event, $availableLocales);
        });

        $builder->addModelTransformer(new CallbackTransformer(function ($data) {
            // from database to form
            if (is_array($data)) {
                return array_keys(array_filter($data));
            }

            return $data;
        }, function ($data) use ($availableLocales) {
            // ensure all available locales are present in the array
            $value = array_combine($availableLocales, array_fill(0, count($availableLocales), false));

            // from form to database
            if (is_array($data)) {
                foreach ($data as $locale) {
                    if (in_array($locale, $availableLocales)) {
                        $value[$locale] = true;
                    }
                }
                $data = $value;
            }

            return $data;
        }));
    }

    public static function onPreSetData(PreSetDataEvent $event, array $availableLocales): void
    {
        $data = $event->getData();

        $initialValue = array_combine($availableLocales, array_fill(0, count($availableLocales), true));

        if (null === $data) {
            $data = $initialValue;
        }

        // migrate legacy format to new one
        $isLegacy = 0 === sizeof($data);
        foreach ($data as $key => $value) {
            if (is_int($key) && is_string($value)) {
                unset($data[$key]);
                $data[$value] = true;
                $isLegacy = true;
            }
        }

        foreach ($availableLocales as $locale) {
            if (!array_key_exists($locale, $data)) {
                $data[$locale] = !$isLegacy;
            }
        }

        $event->setData($data);
    }
}
