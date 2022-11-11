<?php

namespace Softspring\CmsBundle\Form\Type;

use Softspring\CmsBundle\Manager\RouteManagerInterface;
use Softspring\CmsBundle\Model\RouteInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Constraints\Json;

class SymfonyRouteType extends AbstractType
{
    use PropagateLabelFormatTrait {
        finishView as propagateLabelFinishView;
    }

    protected RouterInterface $router;
    protected RouteManagerInterface $routeManager;

    public function __construct(RouterInterface $router, RouteManagerInterface $routeManager)
    {
        $this->router = $router;
        $this->routeManager = $routeManager;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'required' => false,
        ]);

        $resolver->setDefault('restrict_default_attribute', null);
        $resolver->setAllowedTypes('restrict_default_attribute', ['null', 'string', 'array']);
        $resolver->setNormalizer('restrict_default_attribute', function (Options $options, $value) {
            return is_string($value) ? [$value] : ($value ?? []);
        });

        $resolver->setDefault('restrict_patterns', [
            '^_profiler',
            '^_wdt',
        ]);
        $resolver->setAllowedTypes('restrict_patterns', ['string', 'array']);
        $resolver->setNormalizer('restrict_patterns', function (Options $options, $value) {
            return is_string($value) ? [$value] : ($value ?? []);
        });
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('route_name', ChoiceType::class, [
            'required' => $options['required'],
            'choice_translation_domain' => false,
            'choices' => $this->getRoutes($options),
            'choice_label' => function (?Route $route) {
                return $route ? $route->getDefault('_form___route_name') : '';
            },
            'choice_value' => function (?Route $route) {
                return $route ? $route->getDefault('_form___route_name') : '';
            },
            'choice_attr' => function (?Route $route) {
                $attr = [];

                $parameters = [];
                $path = $route->getPath();
                preg_match_all('/(\{(.*)\})/U', $path, $parameters);

                foreach ($parameters[2] ?? [] as $parameter) {
                    $attr["data-route-parameter-$parameter"] = $route->getRequirement($parameter);
                }

                isset($attr['data-route-parameter']) && $attr['data-route-parameter'] = implode(' ;; ', $attr['data-route-parameter']);

                return $attr;
            },
        ]);

        $builder->add('route_params', TextType::class, [
            'constraints' => new Json(),
        ]);

        $builder->addModelTransformer(new CallbackTransformer(function ($value): ?array {
            if (is_array($value) && is_string($value['route_name']) && $this->routes) {
                $value['route_name'] = $this->routes[$value['route_name']] ?? null;
                $value['route_params'] = json_encode($value['route_params']);
            }

            return $value;
        }, function (?array $value): ?array {
            if ($value['route_name'] instanceof Route) {
                $value['route_name'] = $value['route_name']->getDefault('_form___route_name');
                $value['route_params'] = json_decode($value['route_params'], true);
            }

            return $value;
        }));
    }

    protected ?array $routes = null;

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $this->propagateLabelFinishView($view, $form, $options);

        $view->children['route_name']->vars['attr']['data-route-params'] = $view->children['route_params']->vars['id'];
    }

    protected function getRoutes(array $options): array
    {
        if (is_array($this->routes)) {
            return $this->routes;
        }

        $allRoutes = $this->router->getRouteCollection()->all();

        /** @var RouteInterface $cmsRoute */
        foreach ($this->routeManager->getRepository()->findAll() as $cmsRoute) {
            $allRoutes[$cmsRoute->getId()] = new Route('', ['_sfs_cms_reference' => true]);
        }

        $this->routes = array_filter($allRoutes, function (Route $route, string $routeName) use ($options) {
            if (!empty($options['restrict_default_attribute'])) {
                $matches = false;

                foreach ($options['restrict_default_attribute'] as $defaultValueName) {
                    if ($route->hasDefault($defaultValueName)) {
                        $matches |= true;
                    }
                }

                if (!$matches) {
                    return false;
                }
            }

            foreach ($options['restrict_patterns'] as $pattern) {
                if (preg_match("/$pattern/", $routeName)) {
                    return false;
                }
            }

            return true;
        }, ARRAY_FILTER_USE_BOTH);

        foreach ($this->routes as $routeName => $route) {
            $route->setDefault('_form___route_name', $routeName);
        }

        ksort($this->routes);

        return $this->routes;
    }
}
