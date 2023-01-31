<?php

namespace Softspring\CmsBundle\Form\Admin\Block;

use Doctrine\ORM\EntityManagerInterface;
use Softspring\CmsBundle\Form\Type\DynamicFormType;
use Softspring\CmsBundle\Manager\BlockManagerInterface;
use Softspring\CmsBundle\Model\BlockInterface;
use Softspring\CmsBundle\Model\SchedulableContentInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractBlockForm extends AbstractType
{
    protected BlockManagerInterface $manager;

    protected array $blockTypes;

    protected EntityManagerInterface $em;

    public function __construct(BlockManagerInterface $manager, array $blockTypes, EntityManagerInterface $em)
    {
        $this->manager = $manager;
        $this->blockTypes = $blockTypes;
        $this->em = $em;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BlockInterface::class,
            'translation_domain' => 'sfs_cms_blocks',
            'label_format' => 'admin_blocks.form.%name%.label',
            'block_config' => null,
        ]);

        $resolver->setRequired('block_config');

        $resolver->setNormalizer('label_format', function (Options $options, $value) {
            return "admin_{$options['block_config']['_id']}.form.%name%.label";
        });
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name');

        if ($this->manager->getEntityClassReflection()->implementsInterface(SchedulableContentInterface::class)) {
            $builder->add('publishStartDate', DateTimeType::class, [
                'required' => false,
                'html5' => true,
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'model_timezone' => 'UTC',
                'view_timezone' => 'UTC',
            ]);
            $builder->add('publishEndDate', DateTimeType::class, [
                'required' => false,
                'html5' => true,
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'model_timezone' => 'UTC',
                'view_timezone' => 'UTC',
            ]);
        }

        $builder->add('data', DynamicFormType::class, [
            'form_fields' => $options['block_config']['form_fields'],
            'translation_domain' => $options['translation_domain'],
        ]);
    }
}
