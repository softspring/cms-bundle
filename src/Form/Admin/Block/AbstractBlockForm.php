<?php

namespace Softspring\CmsBundle\Form\Admin\Block;

use Doctrine\ORM\EntityManagerInterface;
use Softspring\CmsBundle\Manager\BlockManagerInterface;
use Softspring\CmsBundle\Model\BlockInterface;
use Softspring\CmsBundle\Model\MultiSiteInterface;
use Softspring\CmsBundle\Model\SchedulableContentInterface;
use Softspring\CmsBundle\Model\SiteInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractBlockForm extends AbstractType
{
    /**
     * @var BlockManagerInterface
     */
    protected $manager;

    /**
     * @var array
     */
    protected $blockTypes;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * AbstractBlockForm constructor.
     */
    public function __construct(BlockManagerInterface $manager, array $blockTypes, EntityManagerInterface $em)
    {
        $this->manager = $manager;
        $this->blockTypes = $blockTypes;
        $this->em = $em;
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BlockInterface::class,
            'translation_domain' => 'sfs_cms',
            'content_form' => null,
        ]);

        $resolver->setRequired('content_form');
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
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

        if ($this->manager->getEntityClassReflection()->implementsInterface(MultiSiteInterface::class)) {
            $builder->add('sites', EntityType::class, [
                'class' => SiteInterface::class,
                'multiple' => true,
                'em' => $this->em,
            ]);
        }

        $builder->add('content', $options['content_form']);
    }

    public function formOptions(BlockInterface $block, ?Request $request): array
    {
        return [
            'content_form' => $this->blockTypes[$block->getKey()]['admin_form'],
            'method' => 'POST',
        ];
    }
}
