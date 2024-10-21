<?php

namespace Softspring\CmsBundle\Form\Admin\Route;

use Softspring\CmsBundle\Helper\LocaleHelper;
use Softspring\CmsBundle\Manager\RoutePathManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Intl\Locales;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RoutePathType extends AbstractType
{
    public function __construct(
        protected RoutePathManagerInterface $routePathManager,
        protected LocaleHelper $localeHelper,
        protected ?string $cacheType,
    ) {
    }

    public function getBlockPrefix(): string
    {
        return 'route_path';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => $this->routePathManager->getEntityClass(),
            'languages' => $this->localeHelper->getEnabledLocales(),
            'default_language' => $this->localeHelper->getDefaultLocale(),
        ]);

        $resolver->setRequired('languages');
        $resolver->setAllowedTypes('languages', 'array');
        $resolver->setRequired('default_language');
        $resolver->setAllowedTypes('default_language', 'string');
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('path', TextType::class, [
            'required' => false,
            'empty_data' => '',
            'attr' => [
                'data-route-path' => true,
                'class' => 'sluggize',
            ],
        ]);

        if ('ttl' === $this->cacheType) {
            $builder->add('cacheTtl', IntegerType::class);
        }

        $builder->add('locale', ChoiceType::class, [
            // 'required' => sizeof($options['languages']) > 1,
            'required' => true,
            'choices' => array_combine(array_map(fn ($lang) => Locales::getName($lang), $options['languages']), $options['languages']),
            'choice_translation_domain' => false,
        ]);
    }
}
