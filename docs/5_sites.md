# Sites

## Site locales

A site can be localized with many locales:

```yaml
# config.yaml
site:
    locales: ['en', 'de']
    default_locale: 'en'
```

Keep in mind that every site's locales must be included together in framework configuration:

```yaml
# config/packages/translation.yaml
framework:
  default_locale: en
  enabled_locales: [ en, de, fr, it, es ]
```

## Force https

Symfony provides a https redirect configuration way using SecurityBundle (https://symfony.com/doc/current/security/force_https.html).

SfsCms provides another easy way to configure this redirection for sites:  

```yaml
site:
    https_redirect: true
```

## Configure hostnames

```yaml
site:
    hosts:
        - { domain: 'example.local' }
```

**Use environment variables**

```yaml
site:
    hosts:
        - { domain: '%env(WEB_DOMAIN)%' }
```

### Canonical hostname

One of the hosts can be defined as canonical:

```yaml
site:
    hosts:
        - { domain: '%env(WEB_DOMAIN)%', canonical: true }
        - { domain: 'www.%env(WEB_DOMAIN)%' }
```

Also, other hosts can be configured as redirections to canonical hostname:

```yaml
site:
    hosts:
        - { domain: '%env(WEB_DOMAIN)%', canonical: true }
        - { domain: 'www.%env(WEB_DOMAIN)%', redirect_to_canonical: true }
```

## Locale hostnames or paths

Hosts can define a locale to be selected when using it:  

```yaml
site:
    hosts:
        - { domain: '%env(WEB_DOMAIN)%', locale: 'en' }
        - { domain: 'fr.%env(WEB_DOMAIN)%', locale: 'fr' }
```

Other option is to define locales as paths:

```yaml
site:
    paths:
        - { path: '/en/', locale: 'en' }
        - { path: '/es/', locale: 'es' }
        - { path: '/de/', locale: 'de' }
```

## Error pages

```yaml
site:
    error_pages:
        404:
            es: [ '@site/store/error_pages/404.html.twig' ]
            en: [ '@site/store/error_pages/404.html.twig' ]
        4xx:
            es: [ '@site/store/error_pages/4xx.html.twig' ]
            en: [ '@site/store/error_pages/4xx.html.twig' ]
        5xx:
            es: [ '@site/store/error_pages/5xx.html.twig', '/srv/cms/site/store/error_pages/5xx-es.html' ]
            en: [ '@site/store/error_pages/5xx.html.twig', '/srv/cms/site/store/error_pages/5xx-en.html' ]
```

## Slash route behaviours

**Redirect / route to a specific route depending on the user language**

```yaml
site:
    slash_route:
        behaviour: 'redirect_to_route_with_user_language'
        route: 'home'
```

## Site sitemaps

```yaml
site:
  sitemaps:
      default:
        url: sitemap.xml
        cache_ttl: 300
```

## Allowed content types

As default, "page" contents are included for every configured host.

But it's able to configure additional content types for hosts:

```yaml
# cms/sites/blog/config.yaml
site:
    allowed_content_types: ['page', 'post']
```

## Default site

```yaml
site:
    allowed_content_types: ['page']
    locales: ['es', 'en']
    default_locale: 'es'
    https_redirect: true
    paths:
        - { path: '/es', locale: 'es' }
        - { path: '/en', locale: 'en' }
    error_pages:
        404:
            es: [ '@site/default/error_pages/404.html.twig' ]
            en: [ '@site/default/error_pages/404.html.twig' ]
        4xx:
            es: [ '@site/default/error_pages/4xx.html.twig' ]
            en: [ '@site/default/error_pages/4xx.html.twig' ]
        5xx:
            es: [ '@site/default/error_pages/5xx.html.twig', '/srv/cms/site/default/error_pages/5xx-es.html' ]
            en: [ '@site/default/error_pages/5xx.html.twig', '/srv/cms/site/default/error_pages/5xx-en.html' ]
    slash_route:
        behaviour: 'redirect_to_route_with_user_language'
        route: 'home'
```

## Override default site

Every site default configuration can be overridden for the project:

```yaml
# cms/sites/default/config.yaml
site:
    allowed_content_types: ['page', 'product', 'news', 'faq']
```

## Create custom site

SfsCms supports multisite. It's easy adding a new site creating a config file:

```yaml
# cms/sites/blog/config.yaml
site:
    allowed_content_types: ['article']
    locales: ['es', 'en']
    default_locale: 'es'
    https_redirect: true
    hosts:
      - { domain: 'blog.%env(WEB_DOMAIN)%' }
    paths:
        - { path: '/es', locale: 'es' }
        - { path: '/en', locale: 'en' }
```

## Configuration reference

```yaml
# Default configuration for sites
site:
    # an array containing the identifiers of content types. The special "page" content type must be added if it's needed
    allowed_content_types: ['page']
    
    # an array with every locale that the site needs to use
    locales: ['es']
    
    # default locale
    default_locale: 'es'
    
    # if enabled, redirects http requests to https
    https_redirect: true
    
    # redirect to default path if no one is provided
    locale_path_redirect_if_empty: true
    
    # an [optional] array with extra configuration 
    extra: ~

    # an [optional] array with configured hosts
    hosts:
      - { domain: '<required>', locale: false, scheme: 'https', canonical: false, redirect_to_canonical: false }
    
    # an [optional] array with paths
    paths:
        - { path: '/es', locale: 'es' }
        - { path: '/en', locale: 'en' }
          
    error_pages:
        404:
            es: [ '@site/default/error_pages/404.html.twig' ]
            en: [ '@site/default/error_pages/404.html.twig' ]
        4xx:
            es: [ '@site/default/error_pages/4xx.html.twig' ]
            en: [ '@site/default/error_pages/4xx.html.twig' ]
        5xx:
            es: [ '@site/default/error_pages/5xx.html.twig', '/srv/cms/site/default/error_pages/5xx-es.html' ]
            en: [ '@site/default/error_pages/5xx.html.twig', '/srv/cms/site/default/error_pages/5xx-en.html' ]
    
    slash_route:
        behaviour: 'redirect_to_route_with_user_language'
        route: 'home'
        redirect_code: 301

    sitemaps:
      -
        url: ~
        default_priority: ~
        default_changefreq: ~
        cache_ttl: ~
        alternates: ~

    sitemaps_index:
      url: false 
```

