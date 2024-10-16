<?php

namespace Softspring\CmsBundle\Form\Admin\Content;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Form\Admin\SiteChoiceType;
use Softspring\Component\DoctrinePaginator\Form\PaginatorForm;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentListFilterForm extends PaginatorForm
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

        $resolver->addNormalizer('query_builder', function (Options $options, QueryBuilder $qb) {
            $alias = $qb->getDQLPart('from')[0]->getAlias();
            $qb->select("$alias, pv");
            $qb->leftJoin("{$alias}.publishedVersion", 'pv');

            return $qb;
        });
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder->add('name', TextType::class, [
            'property_path' => '[name__like]',
        ]);

        if (sizeof($this->cmsConfig->getSitesForContent($options['content_config']['_id'])) > 1) {
            $builder->add('sites', SiteChoiceType::class, [
                'required' => false,
                'property_path' => '[sites.id]',
                'multiple' => false,
                'expanded' => false,
                'content' => $options['content_config'],
            ]);
        }

        $builder->add('status', ChoiceType::class, [
            'required' => false,
            'property_path' => '[publishedVersion__is]',
            'choices' => [
                "{$options['content_config']['_id']}.status.draft" => 'null',
                "{$options['content_config']['_id']}.status.published" => 'not_null',
            ],
        ]);

        $builder->add('publishedVersionContent', TextType::class, [
            'required' => false,
            // 'property_path' => '[versions->data__like]',
            'property_path' => '[publishedVersion.data__like]',
        ]);
    }
}
