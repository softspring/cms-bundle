<?php

namespace Softspring\CmsBundle\Form\Type;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Softspring\ImageBundle\Model\ImageInterface;
use Softspring\ImageBundle\Render\ImageRenderer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImageType extends AbstractType
{
    protected EntityManagerInterface $em;
    protected ImageRenderer $imageRenderer;

    public function __construct(EntityManagerInterface $em, ImageRenderer $imageRenderer)
    {
        $this->em = $em;
        $this->imageRenderer = $imageRenderer;
    }

    public function getParent(): ?string
    {
        return EntityType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'class' => ImageInterface::class,
            'em' => $this->em,
            'required' => false,
            'image_types' => [],
            'image_attr' => [],
            'query_builder' => fn (EntityRepository $entityRepository) => $entityRepository->createQueryBuilder('i'),
            'choice_label' => function (ImageInterface $image) {
                return $image->getName();
            },
            'choice_filter' => function (?ImageInterface $image = null) {
                return true;
            },
        ]);

        $resolver->setDefault('query_builder', function (Options $options) {
            return function (EntityRepository $er) use ($options) {
                return $er->createQueryBuilder('i')
                    ->orderBy('i.id', 'ASC')
                    ->andWhere('i.type IN (:types)')
                    ->setParameter('types', array_keys($options['image_types']));
            };
        });

        $resolver->setDefault('choice_attr', function (Options $options) {
            return function (?ImageInterface $image = null) use ($options) {
                if (empty($options['attr']['data-image-preview-input'])) {
                    return [];
                }

                $imageTypes = $options['image_types'];
                $imageType = $imageTypes[$image->getType()];
                $attrs = [];

                foreach ($imageType as $mode => $version) {
                    if ('image' == $mode) {
                        $attrs['data-image-preview-image'] = $this->imageRenderer->renderImage($image, $version, $options['image_attr']);
                    } elseif ('picture' == $mode) {
                        $attrs['data-image-preview-picture'] = $this->imageRenderer->renderPicture($image, $version, $options['image_attr']);
                    } else {
                        throw new \Exception("Bad $mode mode for image_type. Only 'image' and 'picture' are allowed");
                    }
                }

                return $attrs;
            };
        });
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['image_attr'] = $options['image_attr'];
    }
}
