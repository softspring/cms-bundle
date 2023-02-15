# CHANGELOG

## [v5.0.4](https://github.com/softspring/cms-bundle/releases/tag/v5.0.4)

### Upgrading

*Nothing to do on upgrading*

### Commits

- [2d0d69e](https://github.com/softspring/cms-bundle/commit/2d0d69e6677ef3a694ac968438536577d79a7722): BUNDLES-160 - fix missing translations
- [bcdc65b](https://github.com/softspring/cms-bundle/commit/bcdc65b628edb62fd434f768afa205f28578352b): BUNDLES-158 - fix SymfonyRouteType JS
- [cf9b406](https://github.com/softspring/cms-bundle/commit/cf9b4066592d5e5465bb39443b5a62fe58091b82): BUNDLES-159 - fix route_defined twig-extra-bundle function integration with CMS

### Changes

```
 assets/scripts/admin/routes-forms.js  | 27 +++++++++++++++++----------
 cms/contents/page/config.yaml         | 16 ++++++++--------
 src/Routing/CmsRouter.php             |  6 +++---
 src/Routing/UrlGenerator.php          | 13 +++++++++++--
 translations/sfs_cms_admin.en.yaml    |  5 +++++
 translations/sfs_cms_admin.es.yaml    |  5 +++++
 translations/sfs_cms_contents.en.yaml | 15 ++++++++++++++-
 translations/sfs_cms_contents.es.yaml | 15 ++++++++++++++-
 8 files changed, 77 insertions(+), 25 deletions(-)
```

## [v5.0.3](https://github.com/softspring/cms-bundle/releases/tag/v5.0.3)

### Upgrading

*Nothing to do on upgrading*

### Commits

- [c6de737](https://github.com/softspring/cms-bundle/commit/c6de73764001c6522fa98f8fc666c433c2192ca6): Fix code style
- [f17ba61](https://github.com/softspring/cms-bundle/commit/f17ba610e9aba197b30e113566d5384368ed4769): BUNDLES-157 - set locale_filter value for database already stored modules without it

### Changes

```
 src/Form/Module/AbstractModuleType.php | 14 ++++++++++++--
 src/Utils/ModuleMigrator.php           |  2 +-
 2 files changed, 13 insertions(+), 3 deletions(-)
```

## [v5.0.2](https://github.com/softspring/cms-bundle/releases/tag/v5.0.2)

### Upgrading

*Nothing to do on upgrading*

### Commits

- [aaaeb35](https://github.com/softspring/cms-bundle/commit/aaaeb35542437be93476052ed40a2e01e299af22): BUNDLES-152 - Add routeToSymfoyRoute to improve module migrations
- [b13c0ba](https://github.com/softspring/cms-bundle/commit/b13c0ba15e9f680129b8a5abc5b35945d70f2295): BUNDLES-153 - hide route_params in SymfonyRouteType on page load

### Changes

```
 assets/scripts/admin/routes-forms.js |  6 ++++++
 src/Utils/ModuleMigrator.php         | 14 ++++++++++++++
 tests/Utils/ModuleMigratorTest.php   | 28 ++++++++++++++++++++++++++++
 3 files changed, 48 insertions(+)
```

## [v5.0.1](https://github.com/softspring/cms-bundle/releases/tag/v5.0.1)

### Upgrading

*Nothing to do on upgrading*

### Commits

- [c7b7b16](https://github.com/softspring/cms-bundle/commit/c7b7b1609fc06ff19ef8605eb53234e93ffaef99): BUNDLES-151 - [cms] migrate module data on render uncompiled modules
- [e52d246](https://github.com/softspring/cms-bundle/commit/e52d2460084fda6974782b8464efb1c3162a4cd5): Update README.md

### Changes

```
 README.md                           |  2 +-
 src/Form/Traits/DataMapperTrait.php | 14 ++------------
 src/Render/ContentRender.php        |  3 +++
 src/Utils/ModuleMigrator.php        | 15 +++++++++++++++
 4 files changed, 21 insertions(+), 13 deletions(-)
```

## [v5.0.0](https://github.com/softspring/cms-bundle/releases/tag/v5.0.0)

*Previous versions are not in changelog*
