<?php

namespace Softspring\CmsBundle\Form\Admin;

use Softspring\CmsBundle\Helper\CmsHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Event\PreSetDataEvent;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SiteFilterType extends AbstractType
{
    public function __construct(protected CmsHelper $cmsHelper)
    {
    }

    public function getParent(): string
    {
        return SiteChoiceType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'multiple' => true,
            'expanded' => true,
            'block_prefix' => 'module_site_filter',
            'choice_translation_domain' => false,
            'available_sites' => null,
        ]);

        $resolver->setRequired('available_sites');
        $resolver->setAllowedTypes('available_sites', ['array']);

        $resolver->setNormalizer('choices', function (OptionsResolver $options, $value) {
            return empty($value) ? array_combine($options['available_sites'], $options['available_sites']) : $value;
        });
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $availableSites = $options['available_sites'];

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (PreSetDataEvent $event) use ($availableSites): void {
            $data = $event->getData();

            if (null === $data) {
                $data = $availableSites;
            }

            foreach ($data as $key => $value) {
                if (is_string($key) && is_bool($value)) {
                    unset($data[$key]);

                    if ($value) {
                        $data[] = $availableSites[array_search($key, array_map('strval', $availableSites))];
                    }
                }
            }

            $event->setData($data);
        });

        $builder->addModelTransformer(new CallbackTransformer(function ($data) use ($availableSites): array {
            // from database to form
            if (is_array($data)) {
                foreach ($data as $k => $v) {
                    if (is_string($v)) {
                        $data[$k] = $availableSites[array_search($v, array_map('strval', $availableSites))];
                    }
                }
            }

            return array_values($data);
        }, function ($data) use ($availableSites) {
            // ensure all available sites are present in the array
            $value = array_combine($availableSites, array_fill(0, count($availableSites), false));

            // from form to database
            if (is_array($data)) {
                foreach ($data as $site) {
                    if (in_array("$site", $availableSites)) {
                        $value["$site"] = true;
                    }
                }
                $data = $value;
            }

            return $data;
        }));
    }
}
