# Symfony Route type

This **symfonyRoute** type makes reference to *Softspring\CmsBundle\Form\Type\SymfonyRouteType* form type.

This form type provides a way to select a route.

## Fields

**Route name**

A list of Symfony routes' names. This list includes the CMS defined routes.

**Route parameters**

This field accepts a JSON containing attributes for the URL generation.

For example, this Symfony route:

```yaml
# config/routes.yaml
product_big_view:
    path: /product-big/{color}/view
```

requires a "color" parameter. We can define its value with *{"color": "yellow"}*.

## Options

### restrict_patterns

Routes can be filtered by a default value.

Every CMS has a *_sfs_cms_reference* default value defined to be used as filter.

You can flag some routes with a default value to use as filter, for example

```yaml
# config/routes.yaml
my_route:
     defaults:
         use_for_some_content: true
```

Then you can use this value as option in your modules:

```yaml
module:
     module_options:
         form_fields:
             button_link:
                 type: "symfonyRoute"
                 type_options:
                     restrict_default_attribute: "use_for_some_content"
```

Or combine with some others:

```yaml
module:
     module_options:
         form_fields:
             button_link:
                 type: "symfonyRoute"
                 type_options:
                     restrict_default_attribute:
                         - "_sfs_cms_reference"
                         - "use_for_some_content"
                         - "use_for_route_paramsernative_content"
```

### restrict_default_attribute

Also routes can be filtered by patterns using restrict_patterns option.

By default, ^_profiler and ^_wdt patterns are configured.

You can set your own patterns:

```yaml
module:
     module_options:
         form_fields:
             button_link:
                 type: "symfonyRoute"
                 type_options:
                     restrict_patterns:
                         - "^_profiler"
                         - "^_wdt"
                         - "admin"
                         - "^skip_route$"
```

Of course, you can combine both restriction methods ("restrict_default_attribute" and "restrict_patterns").

## Use the value

This type returns an array with both fields:

```php
    $form->get('route')->getData() => [
        'route_name' => 'product_big_view',
        'route_params' => [
            'color' => 'yellow'
        ]       
    ]   
```

This value can be in twig:

```twig
    {{ url(example.route.route_name, example.route.route_params) }}
```

or with PHP:

```php
    $this->router->generate($route['route_name'], $route['route_params']);
```
