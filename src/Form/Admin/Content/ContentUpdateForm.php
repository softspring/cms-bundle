<?php

namespace Softspring\CmsBundle\Form\Admin\Content;

use Doctrine\ORM\EntityManagerInterface;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Form\Admin\DynamicFormType;
use Softspring\CmsBundle\Model\ContentInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentUpdateForm extends AbstractType implements ContentUpdateFormInterface
{
    protected EntityManagerInterface $em;
    protected CmsConfig $cmsConfig;

    public function __construct(EntityManagerInterface $em, CmsConfig $cmsConfig)
    {
        $this->em = $em;
        $this->cmsConfig = $cmsConfig;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ContentInterface::class,
            'label_format' => 'admin_content.form.%name%.label',
            'validation_groups' => ['Default', 'create'],
            'translation_domain' => 'sfs_cms_admin',
            'content' => null,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class);

        if (!empty($options['content']['extra_fields'])) {
            $builder->add('extraData', DynamicFormType::class, [
                'form_fields' => $options['content']['extra_fields'],
            ]);
        }
    }
}
