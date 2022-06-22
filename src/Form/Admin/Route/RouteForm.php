<?php

namespace Softspring\CmsBundle\Form\Admin\Route;

use Doctrine\ORM\EntityManagerInterface;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\RouteInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Constraints\Regex;

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
            ]);

            $builder->add('content', EntityType::class, [
                'class' => ContentInterface::class,
                'required' => false,
                'em' => $this->em,
                'choice_label' => function (ContentInterface $content) {
                    return $content->getName();
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

            $routes = array_keys(array_filter($this->router->getRouteCollection()->all(), function (Route $route) {
                return (bool) $route->getDefault('_sfs_cms_reference');
            }));

            $builder->add('symfonyRoute', ChoiceType::class, [
                'choices' => ['' => null] + array_combine($routes, $routes),
            ]);
        }

        $builder->add('paths', RoutePathCollectionType::class, [
            'entry_options' => [
                'label_format' => $options['label_format'] ? str_replace('%name%.label', 'paths.%name%.label', $options['label_format']) : null,
            ],
        ]);
    }
}
