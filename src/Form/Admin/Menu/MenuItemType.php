<?php

namespace Softspring\CmsBundle\Form\Admin\Menu;

use Doctrine\ORM\EntityManagerInterface;
use Softspring\CmsBundle\Form\Type\SymfonyRouteType;
use Softspring\CmsBundle\Form\Type\TranslatableTextType;
use Softspring\CmsBundle\Manager\MenuItemManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class MenuItemType extends AbstractType
{
    protected MenuItemManagerInterface $menuItemManager;
    protected EntityManagerInterface $em;

    public function __construct(MenuItemManagerInterface $menuItemManager, EntityManagerInterface $em)
    {
        $this->menuItemManager = $menuItemManager;
        $this->em = $em;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => $this->menuItemManager->getEntityClass(),
            'label_format' => 'admin_menus.form.items.%name%.label',
            'translation_domain' => 'sfs_cms_admin',
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('text', TranslatableTextType::class, [
            'constraints' => new NotBlank(),
        ]);

        $builder->add('symfonyRoute', SymfonyRouteType::class, [
            'required' => false,
        ]);
    }
}
