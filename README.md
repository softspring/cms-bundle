# CMS Bundle

[![Latest Stable](https://img.shields.io/packagist/v/softspring/cms-bundle?label=stable&style=flat-square)](https://github.com/softspring/cms-bundle/releases)
[![Latest Unstable](https://img.shields.io/packagist/v/softspring/cms-bundle?label=unstable&style=flat-square&include_prereleases)](https://github.com/softspring/cms-bundle/releases)
[![License](https://img.shields.io/packagist/l/softspring/cms-bundle?style=flat-square)](https://github.com/softspring/cms-bundle/blob/6.0/LICENSE)
[![PHP Version](https://img.shields.io/packagist/dependency-v/softspring/cms-bundle/php?style=flat-square)](https://github.com/softspring/cms-bundle/blob/6.0/composer.json)
[![Downloads](https://img.shields.io/packagist/dt/softspring/cms-bundle?style=flat-square)](https://packagist.org/packages/softspring/cms-bundle)
[![CI](https://img.shields.io/github/actions/workflow/status/softspring/cms-bundle/ci.yml?branch=6.0&style=flat-square&label=CI)](https://github.com/softspring/cms-bundle/actions/workflows/ci.yml)
[![Coverage](https://img.shields.io/codecov/c/github/softspring/cms-bundle?branch=6.0&style=flat-square)](https://app.codecov.io/gh/softspring/cms-bundle/tree/6.0)

Softspring CMS Bundle provides the core CMS runtime and administration screens used by Armonic projects.

## Site context in fragments

Internal and signed ESI fragments carry their site in the `_site` request
attribute. Site resolution restores the enabled site object and the host/path
configuration for the fragment's locale before rendering. Query parameters on
normal pages do not override their site. An explicit unknown or disabled
fragment site fails instead of selecting another site.

Menu rendering respects an explicitly supplied site; otherwise it uses the
current resolved site. Standalone ESI fragments have a site and locale, but no
originating page request. Invalidate cached fragments and pages after deploying
changes to site-context resolution.

## Configured media selection migrations

Module migration scripts can receive optional fourth and fifth arguments:
`CmsConfig $cmsConfig` and `MediaTypesCollection $mediaTypesCollection`. Older
scripts remain valid. Both editor data mapping and module rendering supply this
context.

`MediaSelectionMigrator::migrate($data, $formFields, $mediaTypesCollection)`
repairs restricted `mediaVersion` fields, including translations and dynamic
collections, using the first available choice in configuration order. Valid
selections and unrelated data remain untouched. A configured picture name is
never replaced with a hard-coded `_default`; unresolved references or empty
allow-lists are preserved for editorial resolution.

Requires the media-bundle configured-selection helper; merge that companion PR
before enabling these migrations.

It lets Symfony applications manage sites, localized routes, pages, content versions, layouts, reusable blocks, menus, editable modules, preview rendering, and compiled content storage.

## Module site filters

Module site filters accept persisted site entities as well as site identifiers
and boolean maps. Saving a module through a site selection field therefore
keeps the same rendering behaviour when its data is hydrated again.

## Documentation

Read the usage documentation in [Armonic Documentation](https://armonic.softspring.es/latest/bundles/cms-bundle/install).

## Features

See [FEATURES.md](FEATURES.md) for the functional scope of this package.

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md).

[Report issues](https://github.com/softspring/cms-bundle/issues) and [send Pull Requests](https://github.com/softspring/cms-bundle/pulls)

## Security

See [SECURITY.md](SECURITY.md).

## License

This package is free and released under the [AGPL-3.0 license](LICENSE).
