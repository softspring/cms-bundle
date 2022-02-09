<?php

namespace Softspring\CmsBundle\Form;

use Softspring\CmsBundle\Entity\Embeddable\Seo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SeoType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Seo::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('metaTitle');
        $builder->add('metaDescription');
        $builder->add('metaKeywords');
        $builder->add('noIndex');
        $builder->add('noFollow');
        $builder->add('sitemap');
    }
}
