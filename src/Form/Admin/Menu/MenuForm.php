<?php

namespace Softspring\CmsBundle\Form\Admin\Menu;

use Doctrine\ORM\EntityManagerInterface;
use Softspring\CmsBundle\Model\MenuInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MenuForm extends AbstractType
{
    protected EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MenuInterface::class,
            'label_format' => 'admin_menus.form.%name%.label',
            'translation_domain' => 'sfs_cms_admin',
        ]);

        $resolver->setRequired('menu_config');
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('name', TextType::class);
        $builder->add('items', MenuItemCollectionType::class);
    }
}
