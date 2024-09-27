<?php

namespace Softspring\CmsBundle\Form\Admin\Route;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Form\Admin\SiteChoiceType;
use Softspring\CmsBundle\Model\RouteInterface;
use Softspring\Component\DoctrinePaginator\Form\PaginatorForm;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RouteListFilterForm extends PaginatorForm implements RouteListFilterFormInterface
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
            'label_format' => 'admin_routes.list.filter_form.%name%.label',
            'class' => RouteInterface::class,
            'rpp_valid_values' => [20],
            'rpp_default_value' => 20,
            'order_valid_fields' => ['id'],
            'order_default_value' => 'id',
        ]);

        $resolver->addNormalizer('query_builder', function (Options $options, QueryBuilder $qb) {
            $alias = $qb->getDQLPart('from')[0]->getAlias();
            $qb->select("$alias, sites, content");
            $qb->leftJoin("{$alias}.sites", 'sites');
            $qb->leftJoin("{$alias}.content", 'content');

            return $qb;
        });
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder->add('id', TextType::class, [
            'property_path' => '[id__like]',
        ]);

        if (sizeof($this->cmsConfig->getSites()) > 1) {
            $builder->add('sites', SiteChoiceType::class, [
                'required' => false,
                'property_path' => '[sites.id]',
                'multiple' => false,
                'expanded' => false,
            ]);
        }

        $builder->add('path', TextType::class, [
            'required' => false,
            'property_path' => '[paths.path__like]',
        ]);

        $builder->add('type', ChoiceType::class, [
            'required' => false,
            'property_path' => '[type]',
            'choices' => [
                'admin_routes.form.type.values.content' => RouteInterface::TYPE_CONTENT,
                'admin_routes.form.type.values.redirect_to_route' => RouteInterface::TYPE_REDIRECT_TO_ROUTE,
                'admin_routes.form.type.values.redirect_to_url' => RouteInterface::TYPE_REDIRECT_TO_URL,
                'admin_routes.form.type.values.parent_route' => RouteInterface::TYPE_PARENT_ROUTE,
            ],
        ]);
    }
}
