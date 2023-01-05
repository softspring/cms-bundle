<?php

namespace Softspring\CmsBundle\Form\Admin\Content;

use Doctrine\ORM\EntityManagerInterface;
use Softspring\CmsBundle\Form\Type\SymfonyRouteType;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\RouteInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentDeleteForm extends AbstractType implements ContentUpdateFormInterface
{
    protected EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ContentInterface::class,
            'validation_groups' => ['Default', 'delete'],
            'translation_domain' => 'sfs_cms_contents',
            'content' => null,
        ]);

        $resolver->setNormalizer('label_format', function (Options $options, $value) {
            return "admin_{$options['content']['_id']}.form.%name%.label";
        });
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('type', ChoiceType::class, [
            'mapped' => false,
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
            // 'constraints' => new NotBlank(),
        ]);

        $builder->add('redirectUrl', TextType::class, [
            'mapped' => false,
            'required' => false,
        ]);

        $builder->add('redirectType', ChoiceType::class, [
            'mapped' => false,
            'required' => false,
            'choices' => [
                'admin_routes.form.redirectType.values.temporary' => Response::HTTP_MOVED_PERMANENTLY,
                'admin_routes.form.redirectType.values.permanent' => Response::HTTP_FOUND,
            ],
        ]);

        $builder->add('symfonyRoute', SymfonyRouteType::class, [
            'mapped' => false,
            'required' => false,
            'restrict_patterns' => [
                '^admin_',
                '^sfs_.*admin',
                '^_profiler',
                '^_wdt',
            ],
        ]);
    }
}
