<?php

namespace Softspring\CmsBundle\Form\Admin\ContentVersion;

use Doctrine\ORM\EntityManagerInterface;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\Component\DoctrinePaginator\Form\PaginatorForm;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VersionListFilterForm extends PaginatorForm
{
    protected CmsConfig $cmsConfig;

    public function __construct(EntityManagerInterface $em, CmsConfig $cmsConfig)
    {
        parent::__construct($em);
        $this->cmsConfig = $cmsConfig;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'translation_domain' => 'sfs_cms_contents',
            'rpp_valid_values' => [20],
            'rpp_default_value' => 20,
            'order_valid_fields' => ['name'],
            'order_default_value' => 'name',
        ]);

        $resolver->setRequired('content_config');

        $resolver->setNormalizer('label_format', function (Options $options, $value) {
            return "admin_{$options['content_config']['_id']}.list.filter_form.%name%.label";
        });
    }
}
