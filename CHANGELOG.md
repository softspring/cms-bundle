# CHANGELOG

## [v5.1.0](https://github.com/softspring/cms-bundle/releases/tag/v5.1.0)

### Upgrading

There are too many changes in this version, so you must check your code to adapt it to the new version.

As we noticed in README, this bundle is still under development, so it has changed a lot since the last version.

We are not going to provide a full changelog.

#### Assets installing

Upgrade your package.json to use the appropriated assets:

```json
{
    "dependencies": {
        "@softspring/cms": "file:vendor/softspring/cms-bundle/assets",
        "@softspring/cms-module-collection": "file:vendor/softspring/cms-module-collection/assets",
        "@softspring/media": "file:vendor/softspring/media-bundle/assets",
        "@softspring/polymorphic-form-type": "file:vendor/softspring/polymorphic-form-type/assets"
    }
}
```

Now you can use directly in you JS:

```js
import '@softspring/cms/scripts/admin-cms';
import '@softspring/media/scripts/media-type';
import '@softspring/polymorphic-form-type/scripts/polymorphic-form-type';
import '@softspring/cms-module-collection/modules';
```

#### Doctrine migrations

After 5.1 versions, CMS contains migrations to update the database schema. 

If your version is older than 5.1, probably your database is already created, so ignore the origin migration with:

    bin/console doctrine:migrations:version "Softspring\CmsBundle\Migrations\Version20230301000000" --add --no-interaction

and run a diff to check if there are any changes:

    bin/console doctrine:migrations:diff --namespace=DoctrineMigrations

Take care of the namespace parameter, it must be the same as the one configured in your `doctrine_migrations.yaml` file. 


## [v5.0.5](https://github.com/softspring/cms-bundle/releases/tag/v5.0.5)

### Upgrading

*Nothing to do on upgrading*

### Commits

- [acd70f8](https://github.com/softspring/cms-bundle/commit/acd70f8f31069d5940f394e8b4e1ce991fc248a1): Update changelog
- [62ea59e](https://github.com/softspring/cms-bundle/commit/62ea59e6b3fe9d16a8bbad567e74d20a482e7b4c): BUNDLES-155 - fix content export on readonly filesystems forcing to use tmp dir for temp zip file
- [edf1370](https://github.com/softspring/cms-bundle/commit/edf13705d165c9f0ae79c1809c010e32bc0dcd7c): BUNDLES-156 - fix route deprecation
- [c1ce876](https://github.com/softspring/cms-bundle/commit/c1ce8767f0ec98a27497d227be4e8f29f9521e6d): Add troubleshooting doc page

### Changes

```
 CHANGELOG.md                               | 90 +++++++++++++++++++++++++++++-
 config/routing/admin_content_type.yaml     | 12 ++--
 docs/15_troubleshooting.md                 | 12 ++++
 src/Controller/Admin/ContentController.php |  2 +-
 src/Utils/ZipContent.php                   | 20 +++----
 5 files changed, 117 insertions(+), 19 deletions(-)
```

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
