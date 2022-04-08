<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use <?= $entity_full_class ?>;
use Softspring\CmsBundle\Form\Admin\Content\ContentUpdateForm;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class <?= $class_name ?> extends ContentUpdateForm
{
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => <?= $entity_class ?>::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        // add your custom form fields
    }
}
