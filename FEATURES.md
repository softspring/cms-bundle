# CMS Bundle Features

Functional definition for `softspring/cms-bundle`.

This package provides the core CMS model, configuration loader, administration UI, rendering pipeline, routing integration, and built-in editable CMS elements used by Armonic applications.

## Purpose

- Manage multi-site and multi-locale content in Symfony applications.
- Provide administration screens for CMS content, routes, sites, blocks, and menus.
- Render editable content through layouts, containers, modules, and blocks.
- Keep content rendering extensible through project configuration and reusable CMS collections.

## Main Features

- Site configuration with locales, hosts, paths, HTTPS redirects, sitemaps, error pages, and allowed content types.
- CMS route management with localized paths and route behaviours.
- Page and custom content type support backed by Doctrine entities.
- Content versioning, preview rendering, publishing support, and compiled content storage.
- Layout configuration with named containers and allowed module restrictions.
- Editable module system with built-in module form types and render templates.
- Built-in modules for HTML, translatable HTML, CSS, containers, grids, and block rendering.
- Block management for reusable static or dynamic content fragments.
- Menu management and menu rendering helpers.
- Admin controllers, templates, forms, and menu providers for CMS back-office screens.
- Twig namespaces and CMS collection loading for default, package, and project-level overrides.
- Doctrine mappings, migrations, entity managers, entity transformers, and target entity resolution.
- Request integration through value resolvers and legacy ParamConverter support when available.
- Optional maker commands for generating CMS blocks, contents, layouts, menus, and modules when MakerBundle is installed.
- AssetMapper integration for published admin assets when AssetMapper is available.
- JavaScript and CSS admin assets published from `assets/dist/` and sourced from `assets/`.

## Configuration Scope

The bundle loads CMS definitions from ordered collections:

1. the default collection shipped in `vendor/softspring/cms-bundle/cms`
2. configured external collections
3. the project `cms/` directory

Later collections override earlier definitions with the same element identifier.

## Extension Points

- Add CMS collections through `sfs_cms.collections`.
- Override default sites, layouts, modules, contents, menus, and blocks in the project `cms/` directory.
- Provide custom Doctrine entity classes for sites, routes, content, content versions, pages, menus, menu items, and blocks.
- Add custom content types backed by project entities.
- Add custom module form types, edit templates, form templates, and render templates.
- Register CMS plugins using the plugin infrastructure.
- Override Twig templates through Symfony template resolution and configured CMS namespaces.

## Current Limits

- The package expects a Symfony application with Doctrine ORM and Twig integration.
- The admin UI depends on other Softspring bundles for shared components, media handling, permissions, forms, and user integration.
- Some compatibility services still support older ParamConverter integration when SensioFrameworkExtraBundle is installed.
- Asset builds require npm dependencies and the local Softspring asset packages declared in `package.json`.
