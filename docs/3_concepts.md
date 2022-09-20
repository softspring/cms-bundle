# Concepts

To begin using the SfsCms you must know some concepts explained next.

## Elements

### Sites

A site defines a *webspace* or application that needs to serve contents on it. 

Every project using SfsCms has at least the **default** site.

For example, a project can define a web page, blog, faqs. Also, every content can be hosted in the same site. 

Sites are distinguished by host or paths.

Sites can configure different languages, different sitemaps, and some special behaviours.

Contents and routes will be site dependant.

### Layout

A layout is a way to show contents. Put it in some way, is a kind of web structure.

A layout can define different areas to show contents, onwards we'll call them **containers**.

A layout has its own twig template as base template for contents using it. So, menus, sidebars, etc, can be defined on it. 

### Contents

#### Pages

A page is an instance of contents to be shown when a user visit a URL.

It's linked to a layout that defines its structure. 

#### Versions

When a page is edited, a new content version is created. This allows to keep the history of versions along the time.

#### Custom content types

A page is the basic kind of content, but, the SfsCms provides the feature of creating **custom content types**.

A custom content type is basically the same as pages, but the main difference is that it **is stored in a different 
 doctrine entity**. This allows to define some content type dependencies. Also, it is able to define different structures
 and templates.

Sites can define witch content types are allowed to be created for them.

Let's see some examples:

A blog post can be defined in a custom content type. We can define App\Entity\Post as the doctrine entity, and define a 
 relationship to an author. Author info may be used when editing or rendering the post.

Other example can be a product content type for a store site. The product content type could be linked to an existing
 product entity and use its contents and data.

### Routes

A route allows to reach contents from URLs. It contains a collection of paths or slugs (linked or not to languages) and 
 defines a behaviour to be performed.

The basic behaviour is to serve a content (page or content type), but it can be configured to act as a redirection, ...

### Modules

A module is the minimum unit of content that can be added to a page or content type. A module can be a title block, a card, hero,
 containers and grids, etc.

One of the SfsCms best and powerful features is that custom modules can be created easily.

Also, it is posible to add modules collections.

### Blocks

A block is a piece of content that can be used in many pages, content types, or directly from twig templates. 

Blocks can be defined as static (just twig code, not editable) or dynamic, and can be singleton or many times instantiable. 
 This means that an instance of a block can be created just once or many times.

### Menus

A menu is a list of links with text and links (linked to a route) rendered in a template.

Menus can be rendered in any template with a twig function, as a special type of block.

## How it works

### Editing contents

### SfsCms Router

### Rendering contents

### Caches

