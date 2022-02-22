<?php

namespace Softspring\CmsBundle\Form\Admin\Page;

use Doctrine\ORM\EntityManagerInterface;
use Softspring\CmsBundle\Model\LayoutInterface;
use Softspring\CmsBundle\Model\PageInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PageUpdateForm extends AbstractType implements PageUpdateFormInterface
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
            'validation_groups' => ['Default', 'update'],
            'translation_domain' => 'sfs_cms',
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class);

        $builder->add('layout', EntityType::class, [
            'required' => false,
            'class' => LayoutInterface::class,
            'em' => $this->em,
            'choice_label' => function (LayoutInterface $layout) {
                return $layout->getName();
            },
        ]);
    }
}
