<?php

namespace Softspring\CmsBundle\Form\Admin\Route;

use Doctrine\ORM\EntityManagerInterface;
use Softspring\CmsBundle\Form\Admin\SiteChoiceType;
use Softspring\CmsBundle\Form\Type\SymfonyRouteType;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\RouteInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class RouteForm extends AbstractType
{
    protected EntityManagerInterface $em;
    protected RouterInterface $router;

    public function __construct(EntityManagerInterface $em, RouterInterface $router)
    {
        $this->em = $em;
        $this->router = $router;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RouteInterface::class,
            'content_relative' => false,
            'label_format' => 'admin_routes.form.%name%.label',
            'translation_domain' => 'sfs_cms_admin',
            'constraints' => new Callback(function (RouteInterface $value, ExecutionContextInterface $context, $payload) {
                if ($value->getContent() && $value->getContent()->getSite() !== $value->getSite()) {
                    $context->buildViolation('The content must have the same site as route selected one')
                        ->atPath('content')
                        ->addViolation()
                    ;
                }
            }),
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id', TextType::class, [
            'constraints' => new Regex('/^[a-z][a-z0-9_]{3,}$/i'),
            'attr' => [
                'class' => 'snake-case',
                'data-route-id' => true,
            ],
        ]);

        if (!$options['content_relative']) {
            $builder->add('site', SiteChoiceType::class);

            $builder->add('type', ChoiceType::class, [
                'choices' => [
                    //                'PAGE' => RouteInterface::TYPE_PAGE,
                    'admin_routes.form.type.values.content' => RouteInterface::TYPE_CONTENT,
                    //                'STATIC' => RouteInterface::TYPE_STATIC,
                    //                'NOT_FOUND' => RouteInterface::TYPE_NOT_FOUND,
                    'admin_routes.form.type.values.redirect_to_route' => RouteInterface::TYPE_REDIRECT_TO_ROUTE,
                    'admin_routes.form.type.values.redirect_to_url' => RouteInterface::TYPE_REDIRECT_TO_URL,
                    //                'PARENT_ROUTE' => RouteInterface::TYPE_PARENT_ROUTE,
                ],
                'choice_attr' => function ($value) {
                    return [
                        'data-content-visible' => in_array($value, [RouteInterface::TYPE_CONTENT]) ? 'visible' : 'hidden',
                        'data-redirect-url-visible' => in_array($value, [RouteInterface::TYPE_REDIRECT_TO_URL]) ? 'visible' : 'hidden',
                        'data-redirect-type-visible' => in_array($value, [RouteInterface::TYPE_REDIRECT_TO_URL, RouteInterface::TYPE_REDIRECT_TO_ROUTE]) ? 'visible' : 'hidden',
                        'data-symfony-route-visible' => in_array($value, [RouteInterface::TYPE_REDIRECT_TO_ROUTE]) ? 'visible' : 'hidden',
                    ];
                }
            ]);

            $builder->add('content', EntityType::class, [
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
                // 'constraints' => new NotBlank(),
            ]);

            $builder->add('redirectUrl', TextType::class, [
                'required' => false,
            ]);

            $builder->add('redirectType', ChoiceType::class, [
                'required' => false,
                'choices' => [
                    'admin_routes.form.redirectType.values.temporary' => Response::HTTP_MOVED_PERMANENTLY,
                    'admin_routes.form.redirectType.values.permanent' => Response::HTTP_FOUND,
                ],
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
