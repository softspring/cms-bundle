<?php

namespace Softspring\CmsBundle\Form\Admin\Route;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Softspring\CmsBundle\Form\Admin\SiteChoiceType;
use Softspring\CmsBundle\Form\Type\SymfonyRouteType;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\RouteInterface;
use Softspring\CmsBundle\Model\SiteInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

abstract class AbstractRouteForm extends AbstractType
{
    public function __construct(protected EntityManagerInterface $em, protected RouterInterface $router)
    {
    }

    public function getBlockPrefix(): string
    {
        return 'route';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RouteInterface::class,
            'content_relative' => false,
            'label_format' => 'admin_routes.form.%name%.label',
            'translation_domain' => 'sfs_cms_admin',
            'constraints' => new Callback(function (RouteInterface $value, ExecutionContextInterface $context, $payload) {
                if ($value->getContent()) {
                    foreach ($value->getSites() as $site) {
                        if (!$value->getContent()->getSites()->contains($site)) {
                            $context->buildViolation('The content must have the same sites as route selected ones')
                                ->atPath('sites')
                                ->addViolation();
                            break;
                        }
                    }
                }
            }),
            'disabled_id' => false,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('id', TextType::class, [
            'constraints' => new Regex('/^[a-z][a-z0-9_]{3,}$/i'),
            'attr' => [
                'class' => 'snake-case',
                'data-route-id' => true,
            ],
            'disabled' => $options['disabled_id'],
        ]);

        $builder->add('parent', EntityType::class, [
            'class' => RouteInterface::class,
            'required' => false,
            'em' => $this->em,
            'choice_filter' => function (?RouteInterface $parent) {
                return !$parent || RouteInterface::TYPE_PARENT_ROUTE == $parent->getType();
            },
            'choice_label' => function (RouteInterface $parent) {
                return $parent->getId();
            },
            'choice_attr' => function (RouteInterface $parent) {
                return [
                    'data-site' => implode(',', $parent->getSites()->map(fn (SiteInterface $site) => "$site")->toArray()),
                ];
            },
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('r')
                    ->select('r, s, c')
                    ->leftJoin('r.content', 'c')
                    ->leftJoin('r.sites', 's')
                    ->orderBy('r.id', 'ASC');
            },
        ]);

        if (!$options['content_relative']) {
            $builder->add('sites', SiteChoiceType::class, [
                'by_reference' => false,
                'expanded' => true,
                'constraints' => new Length(['min' => 1]),
            ]);

            $builder->add('type', ChoiceType::class, [
                'choices' => [
                    'admin_routes.form.type.values.content' => RouteInterface::TYPE_CONTENT,
                    //                'STATIC' => RouteInterface::TYPE_STATIC,
                    //                'NOT_FOUND' => RouteInterface::TYPE_NOT_FOUND,
                    'admin_routes.form.type.values.redirect_to_route' => RouteInterface::TYPE_REDIRECT_TO_ROUTE,
                    'admin_routes.form.type.values.redirect_to_url' => RouteInterface::TYPE_REDIRECT_TO_URL,
                    'admin_routes.form.type.values.parent_route' => RouteInterface::TYPE_PARENT_ROUTE,
                ],
                'choice_attr' => function ($value) {
                    return [
                        'data-show-fields' => match ($value) {
                            RouteInterface::TYPE_CONTENT => 'content',
                            RouteInterface::TYPE_REDIRECT_TO_URL => 'redirectUrl',
                            RouteInterface::TYPE_REDIRECT_TO_ROUTE => 'redirectType,symfonyRoute',
                            RouteInterface::TYPE_PARENT_ROUTE => '',
                            default => '',
                        },
                        'data-hide-fields' => match ($value) {
                            RouteInterface::TYPE_CONTENT => 'redirectUrl,redirectType,symfonyRoute',
                            RouteInterface::TYPE_REDIRECT_TO_URL => 'content,redirectType,symfonyRoute',
                            RouteInterface::TYPE_REDIRECT_TO_ROUTE => 'content,redirectUrl',
                            RouteInterface::TYPE_PARENT_ROUTE => 'content,redirectUrl,redirectType,symfonyRoute',
                            default => '',
                        },
                        'data-empty-fields' => match ($value) {
                            RouteInterface::TYPE_CONTENT => 'redirectUrl,redirectType,symfonyRoute',
                            RouteInterface::TYPE_REDIRECT_TO_URL => 'content,redirectType,symfonyRoute',
                            RouteInterface::TYPE_REDIRECT_TO_ROUTE => 'content,redirectUrl',
                            RouteInterface::TYPE_PARENT_ROUTE => 'content,redirectUrl,redirectType,symfonyRoute',
                            default => '',
                        },
                    ];
                },
            ]);

            $builder->add('content', EntityType::class, [
                'class' => ContentInterface::class,
                'required' => false,
                'em' => $this->em,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->select('c, s')
                        ->leftJoin('c.sites', 's')
                        ->orderBy('c.name', 'ASC');
                },
                'choice_label' => function (ContentInterface $content) {
                    return $content->getName();
                },
                'choice_attr' => function (ContentInterface $content) {
                    return [
                        'data-site' => implode(',', $content->getSites()->map(fn (SiteInterface $site) => $site->getId())->toArray()),
                    ];
                },
                // 'constraints' => new NotBlank(),
            ]);

            $builder->add('redirectUrl', TextType::class, [
                'required' => false,
            ]);

            $builder->add('redirectType', ChoiceType::class, [
                'required' => false,
                'choices' => [
                    'admin_routes.form.redirectType.values.temporary' => Response::HTTP_FOUND,
                    'admin_routes.form.redirectType.values.permanent' => Response::HTTP_MOVED_PERMANENTLY,
                ],
                // 'constraints' => new NotBlank(),
            ]);

            $builder->add('symfonyRoute', SymfonyRouteType::class, [
                'required' => false,
                'restrict_patterns' => [
                    '^admin_',
                    '^sfs_.*admin',
                    '^_profiler',
                    '^_wdt',
                ],
            ]);
        }

        $builder->add('paths', RoutePathCollectionType::class, [
            'entry_options' => [
                'label_format' => $options['label_format'] ? str_replace('%name%.label', 'paths.%name%.label', $options['label_format']) : null,
            ],
        ]);
    }
}
