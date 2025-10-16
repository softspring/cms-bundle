<?php

namespace Softspring\CmsBundle\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolver;

class BlockTypeType extends BlockStaticType
{
    public function getBlockPrefix(): string
    {
        return 'block_type';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'choice_filter' => function (?object $blockConfig) {
                return $blockConfig && !$blockConfig->static;
            },
        ]);
    }
}
