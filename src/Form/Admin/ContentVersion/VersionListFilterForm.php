<?php

namespace Softspring\CmsBundle\Form\Admin\ContentVersion;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\ContentVersionInterface;
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
            'rpp_valid_values' => [10],
            'rpp_default_value' => 10,
            'order_valid_fields' => ['versionNumber'],
            'order_default_value' => 'versionNumber',
            'order_direction_default_value' => 'desc',
            'class' => ContentVersionInterface::class
        ]);

        $resolver->setRequired('content');
        $resolver->setAllowedTypes('content', [ContentInterface::class]);

        $resolver->setRequired('content_config');

        $resolver->setNormalizer('label_format', function (Options $options, $value) {
            return "admin_{$options['content_config']['_id']}.list.filter_form.%name%.label";
        });

        $resolver->addNormalizer('query_builder', function (Options $options, QueryBuilder $qb) {
            $alias = $qb->getDQLPart('from')[0]->getAlias();
            $qb->andWhere("$alias.content = :content");
            $qb->setParameter('content', $options['content']);

            return $qb;
        });
    }
}
