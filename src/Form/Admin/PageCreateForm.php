<?php

namespace Softspring\CmsBundle\Form\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Softspring\CmsBundle\Model\PageInterface;
use Softspring\CmsBundle\Model\SiteInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PageCreateForm extends AbstractType implements PageCreateFormInterface
{
    protected EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PageInterface::class,
            'label_format' => 'admin_pages.form.%name%.label',
            'validation_groups' => ['Default', 'create'],
            'translation_domain' => 'sfs_cms',
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class);

        // if ($this->manager->getEntityClassReflection()->implementsInterface(SiteReferenceInterface::class)) {
        $builder->add('site', EntityType::class, [
            // 'class' => SiteInterface::class,
            'required' => false,
            'class' => SiteInterface::class,
            'em' => $this->em,
            'choice_label' => function (SiteInterface $site) {
                return $site->getName();
            },
        ]);
        // }
    }
}
