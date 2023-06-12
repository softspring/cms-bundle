<?php

namespace Softspring\CmsBundle\Form\Type;

use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Form\DynamicFormTrait;
use Softspring\CmsBundle\Model\SiteInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SiteableType extends AbstractType
{
    use DynamicFormTrait;

    protected CmsConfig $cmsConfig;

    public function __construct(CmsConfig $cmsConfig)
    {
        $this->cmsConfig = $cmsConfig;
    }

    public function getBlockPrefix(): string
    {
        return 'siteable';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'required' => false,
            'sites' => $this->cmsConfig->getSites(),
            'children_attr' => [],
            'type' => 'text',
            'type_options' => [],
        ]);

        $resolver->setRequired('sites');
        $resolver->setAllowedTypes('sites', 'array');
        $resolver->setRequired('type');
        $resolver->setAllowedTypes('type', 'string');
        $resolver->setAllowedTypes('type_options', 'array');
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var SiteInterface $site */
        foreach ($options['sites'] as $site) {
            $builder->add($site->getId(), $this->getFieldType($options['type']), [
                'label' => $site->getId().'.name',
                'translation_domain' => 'sfs_cms_sites',
                'block_prefix' => 'siteable_element',
            ] + $options['type_options']
            + [
                'attr' => $options['children_attr'] + ($options['type_options']['attr'] ?? []),
            ]);
        }
    }
}
