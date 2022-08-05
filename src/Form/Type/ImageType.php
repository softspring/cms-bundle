<?php

namespace Softspring\CmsBundle\Form\Type;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Softspring\MediaBundle\Model\MediaInterface;
use Softspring\MediaBundle\Render\MediaRenderer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImageType extends AbstractType
{
    protected EntityManagerInterface $em;
    protected MediaRenderer $imageRenderer;

    public function __construct(EntityManagerInterface $em, MediaRenderer $imageRenderer)
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
            'class' => MediaInterface::class,
            'em' => $this->em,
            'required' => false,
            'media_types' => [],
            'media_attr' => [],
            'query_builder' => fn (EntityRepository $entityRepository) => $entityRepository->createQueryBuilder('i'),
            'choice_label' => function (MediaInterface $image) {
                return $image->getName();
            },
            'choice_filter' => function (?MediaInterface $image = null) {
                return true;
            },
        ]);

        $resolver->setDefault('query_builder', function (Options $options) {
            return function (EntityRepository $er) use ($options) {
                return $er->createQueryBuilder('i')
                    ->orderBy('i.id', 'ASC')
                    ->andWhere('i.type IN (:types)')
                    ->setParameter('types', array_keys($options['media_types']));
            };
        });

        $resolver->setDefault('choice_attr', function (Options $options) {
            return function (?MediaInterface $image = null) use ($options) {
                if (empty($options['attr']['data-image-preview-input'])) {
                    return [];
                }

                $imageTypes = $options['media_types'];
                $imageType = $imageTypes[$image->getType()];
                $attrs = [];

                foreach ($imageType as $mode => $version) {
                    if ('image' == $mode) {
                        $attrs['data-image-preview-image'] = $this->imageRenderer->renderImage($image, $version, $options['media_attr']);
                    } elseif ('picture' == $mode) {
                        $attrs['data-image-preview-picture'] = $this->imageRenderer->renderPicture($image, $version, $options['media_attr']);
                    } else {
                        throw new \Exception("Bad $mode mode for media_type. Only 'image' and 'picture' are allowed");
                    }
                }

                return $attrs;
            };
        });
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['media_attr'] = $options['media_attr'];
    }
}
