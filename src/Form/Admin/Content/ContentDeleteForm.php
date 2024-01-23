<?php

namespace Softspring\CmsBundle\Form\Admin\Content;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Softspring\CmsBundle\Form\Type\SymfonyRouteType;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\RouteInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\When;

class ContentDeleteForm extends AbstractType implements ContentUpdateFormInterface
{
    protected EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ContentInterface::class,
            'validation_groups' => ['Default', 'delete'],
            'translation_domain' => 'sfs_cms_contents',
            'content' => null,
            'entity' => null,
        ]);

        $resolver->setAllowedTypes('entity', ContentInterface::class);

        $resolver->setNormalizer('label_format', function (Options $options, $value) {
            return "admin_{$options['content']['_id']}.delete.form.%name%.label";
        });
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('action', ChoiceType::class, [
            'mapped' => false,
            'expanded' => true,
            'required' => true,
            'choices' => [
                "admin_{$options['content']['_id']}.delete.form.action.options.delete" => 'delete',
                "admin_{$options['content']['_id']}.delete.form.action.options.change" => 'change',
                "admin_{$options['content']['_id']}.delete.form.action.options.redirect" => 'redirect',
            ],
            'choice_attr' => function ($value) {
                return [
                    'data-content-visible' => in_array($value, ['change']) ? 'visible' : 'hidden',
                    'data-symfony-route-visible' => in_array($value, ['redirect']) ? 'visible' : 'hidden',
                ];
            },
        ]);

        $builder->add('content', EntityType::class, [
            'mapped' => false,
            'class' => ContentInterface::class,
            'required' => false,
            'em' => $this->em,
            'choice_label' => function (ContentInterface $content) {
                return $content->getName();
            },
            'choice_attr' => function (ContentInterface $content) {
                return [
                    'data-site' => $content->getSite(),
                ];
            },
            'query_builder' => function (EntityRepository $entityRepository) use ($options) {
                $qb = $entityRepository->createQueryBuilder('c');
                $qb->andWhere('c.id != :currentContent')->setParameter('currentContent', $options['entity']->getId());

                return $qb;
            },
            'constraints' => new When('this.getParent().get("action").getData() == "change"', [new NotBlank()]),
        ]);

        $restrictPatterns = [
            '^admin_',
            '^sfs_.*admin',
            '^_profiler',
            '^_wdt',
        ];

        /** @var RouteInterface $route */
        foreach ($options['entity']->getRoutes() as $route) {
            $restrictPatterns[] = "^{$route->getId()}$";
        }

        $builder->add('symfonyRoute', SymfonyRouteType::class, [
            'mapped' => false,
            'required' => false,
            'restrict_patterns' => $restrictPatterns,
            'route_name_constraints' => new When('this.getParent().getParent().get("action").getData() == "redirect"', [new NotBlank()]),
        ]);
    }
}
