# Getting started

## Load welcome fixtures

*TODO: load welcome fixtures*

## Admin routes

```yaml
# config/routes.yaml
_sfs_cms_pages_:
    resource: "@SfsCmsBundle/config/routing/admin_pages.yaml"
    prefix: "/admin/cms/pages"

#_sfs_cms_content_type_custom_:
#    resource: "@SfsCmsBundle/config/routing/admin_content_type.yaml"
#    prefix: "/admin/cms/customs"
#    defaults:
#        _content_type: custom
#    name_prefix: sfs_cms_admin_content_custom_

_sfs_cms_routes_:
    resource: "@SfsCmsBundle/config/routing/admin_routes.yaml"
    prefix: "/admin/cms/routes"

_sfs_cms_menus_:
    resource: "@SfsCmsBundle/config/routing/admin_menus.yaml"
    prefix: "/admin/cms/menus"

_sfs_cms_blocks_:
    resource: "@SfsCmsBundle/config/routing/admin_blocks.yaml"
    prefix: "/admin/cms/blocks"
```


