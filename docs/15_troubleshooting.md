# Troubleshooting

## Mysql memory allocation error

In some situations, Doctrine can dispatch a "Doctrine\DBAL\Exception\DriverException" exception with an "out of sort memory" message.

> SQLSTATE[HY001]: Memory allocation error: 1038 Out of sort memory, consider increasing server sort buffer size

This is a common error if database instance has a low value for [sort_buffer_size](https://dev.mysql.com/doc/refman/8.0/en/server-system-variables.html#sysvar_sort_buffer_size) variable.

The solution is easy: increase this value, for example to 5.3k.

## Silent fail using a lot of ESI blocks or menus and http cache

If you use a lot of menus (sometimes about 20-30 items) you can experiment a silent error. 

You can try disabling tracing in http_cache:

```yaml
# config/packages/framework.yaml
framework:
    http_cache:
        trace_level: none
    esi: true
```

## After deploying a new version assets are not loaded

SfsCms compiles contents, and stores them in database by default to be used later and improve performance.

Sometimes, after an assets build for deploy, you can experience that assets are not loaded. This is because the 
 content is compiled and stored in database, and it includes assets references.

**Re-publish solution**

One solution is to re-publish the content version in admin panel.

**Disable content compilation storing**

Another solution is to disable content compilation storing in general:

```yaml
# config/packages/sfs_cms.yaml
sfs_cms:
    content:
        save_compiled: false
```

or for a specific layout:

```yaml
# cms/layouts/<your-layout>/config.yaml
layout:
    save_compiled: false
```

or for a specific content type:

```yaml
# cms/contents/<your-content-type>/config.yaml
content:
    save_compiled: false
```

**Prefix compiled contents by version**

The last solution is to prefix compiled contents by version, so when you deploy a new version, the content is not found
 and is compiled and stored again.

```yaml
# config/packages/sfs_cms.yaml
sfs_cms:
    content:
        prefix_compiled: '%env(APP_VERSION)%/'
```

## Some site or locale content render has not styles or scripts

When a content is saved or republished, the content is compiled and stored in database. This content includes styles and scripts references.

If you use webpack encore, when an asset reference is rendered in a request, it won't be rendered again in the same request. 

SfsCms manage the _default webpack entrypoint, and automatically reset the entrypoint when a content is rendered.

If you use a custom entrypoint, you need to reset the entry for the site or locale to force the assets to be recompiled before using it.

```twig
{{ sfs_cms_encore_entry_reset('blog') }}
{{ encore_entry_link_tags('blog-styles', null, 'blog') }}
```
