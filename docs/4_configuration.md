# Configuration

## Configuration structure

After install SfsCms a new "cms" folder is created on project root. This new folder will contain the SfsCms configuration
 for the project.

Inside this directory, other directories will be created:

- **cms/blocks**: blocks configuration
- **cms/contents**: content types configuration
- **cms/fixtures**: this directory will contain contents fixtures
- **cms/layouts**: layouts configuration
- **cms/menus**: menus configuration
- **cms/modules**: modules configuration
- **cms/sites**: sites configuration

## How create elements

Inside the configuration directories (blocks, contents, layouts, ...) a new directory, with the element identifier name,
 needs to be created.

These element directories must contain a config.yaml configuration file, in addition to other templates or required files 
 for the element.

For example, if a new "Call to action" module needs to be created, this is the common files:

- *cms/*
  - *modules/*
    - *call_to_action/*
      - *edit.html.twig*
      - *form.html.twig*
      - *config.yaml*
      - *render.html.twig*

## Configuration overriding

Is posible to override every element configuration, using the same element name in folders.

SfsCms bundle comes with some default elements (a default site, a default layout, and some basic modules).

Creating a *cms/sites/default/config.yaml* file will override the default configuration. The same with other elements.

Also is posible to add many elements collections (as shown later). Any of the provided collection elements can be modified.

The overriding preference is as follows:

1. SfsCms default configuration
2. collections configuration
3. project configuration

Likewise, twig template namespaces work with the same overriding preference. 


