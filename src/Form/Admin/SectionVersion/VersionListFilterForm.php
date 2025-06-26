<?php

namespace Softspring\CmsBundle\Form\Admin\SectionVersion;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Model\SectionInterface;
use Softspring\CmsBundle\Model\SectionVersionInterface;
use Softspring\Component\DoctrinePaginator\Form\PaginatorForm;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VersionListFilterForm extends PaginatorForm implements VersionListFilterFormInterface
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
            'translation_domain' => 'sfs_cms_admin',
            'rpp_valid_values' => [10],
            'rpp_default_value' => 10,
            'order_valid_fields' => ['versionNumber'],
            'order_default_value' => 'versionNumber',
            'order_direction_default_value' => 'desc',
            'class' => SectionVersionInterface::class,
            'label_format' => 'admin_sections.list.filter_form.%name%.label',
        ]);

        $resolver->setRequired('section');
        $resolver->setAllowedTypes('section', [SectionInterface::class]);

        $resolver->addNormalizer('query_builder', function (Options $options, QueryBuilder $qb) {
            $alias = $qb->getDQLPart('from')[0]->getAlias();
            $qb->andWhere("$alias.section = :section");
            $qb->setParameter('section', $options['section']);

            return $qb;
        });
    }
}
