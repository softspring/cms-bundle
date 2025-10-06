<?php

namespace Softspring\CmsBundle\Form\Admin;

use Softspring\CmsBundle\Helper\CmsHelper;
use Softspring\CmsBundle\Model\ContentInterface;
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
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $allAvailableSites = $options['content'] instanceof ContentInterface ? $options['content']->getSites()->toArray() : $this->cmsHelper->config()->getSites();

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (PreSetDataEvent $event) use ($allAvailableSites) {
            $data = $event->getData();

            if (null === $data) {
                $data = $allAvailableSites;
            }

            foreach ($data as $key => $value) {
                if (is_string($key) && is_bool($value)) {
                    unset($data[$key]);

                    if ($value) {
                        $data[] = $allAvailableSites[array_search($key, array_map('strval', $allAvailableSites))];
                    }
                }
            }

            $event->setData($data);
        });

        $builder->addModelTransformer(new CallbackTransformer(function ($data) use ($allAvailableSites) {
            // from database to form
            if (is_array($data)) {
                foreach ($data as $k => $v) {
                    if (is_string($v)) {
                        $data[$k] = $allAvailableSites[array_search($v, array_map('strval', $allAvailableSites))];
                    }
                }
            }

            return array_values($data);
        }, function ($data) use ($allAvailableSites) {
            // ensure all available locales are present in the array
            $value = array_combine($allAvailableSites, array_fill(0, count($allAvailableSites), false));

            // from form to database
            if (is_array($data)) {
                foreach ($data as $site) {
                    if (in_array("$site", $allAvailableSites)) {
                        $value["$site"] = true;
                    }
                }
                $data = $value;
            }

            return $data;
        }));
    }
}
