<?php

namespace Softspring\CmsBundle\Form\Modules;

use Doctrine\ORM\EntityManagerInterface;
use Softspring\CmsBundle\Model\BlockInterface;
use Softspring\PolymorphicFormType\Form\Type\Node\AbstractNodeType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class BlockModuleType extends AbstractNodeType
{
    protected EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getBlockPrefix()
    {
        return 'cms_module_block';
    }

    protected function configureChildOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label_format' => 'modules.block.%name%.label',
            'translation_domain' => 'sfs_cms',
            'prototype_button_label' => 'modules.block.prototype_button',
            'prototype_button_attr' => [ 'class' => 'dropdown-item btn btn-light' ],
        ]);
    }

    protected function buildChildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('block', EntityType::class, [
            'class' => BlockInterface::class,
            'required' => false,
            'em' => $this->em,
            'choice_label' => function (BlockInterface $block) {
                return $block->getKey();
            },
            'constraints' => new NotBlank(),
        ]);
    }
}