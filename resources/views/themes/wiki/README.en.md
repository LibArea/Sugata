# "wiki" theme

The default theme of the public site. Other themes (e.g. `blog`) inherit from it.

## Role

- It is the **root of the theme chain**: child themes (`themes/blog`) inherit every
  file from `wiki` unless they override it themselves.
- If the active theme from the config does not exist or is broken, the resolver
  **always falls back to `wiki`** (an untouchable base that must not be deleted).
- Provides the default styling and the set of pages/components for the
  facts / articles / sites / pages sections.

## Folder structure

```
resources/views/themes/wiki/
├── theme.php                    theme manifest
└── site/
    ├── layouts/
    │   ├── header.php           shared skeleton: <head>, app.css and theme assets
    │   ├── footer.php           footer + Yandex.Metrika + lightbox
    │   ├── article.php          shared layout for article/fact/site/page view
    │   ├── sidebar.php          side column (helper)
    │   └── partials/
    │       └── header-body.php  "body" of the header + nav (overridden by themes)
    ├── pages/
    │   ├── home.php             home page
    │   ├── best.php             "best" with pagination
    │   ├── page.php             utility page ("about", etc.)
    │   ├── facts/{index,view}.php    facts list/view
    │   ├── articles/{index,view}.php articles list/view
    │   └── sites/{index,view}.php    sites list/view
    └── components/
        ├── article-body.php     article body (content parser)
        ├── backlinks.php        "referring pages"
        ├── categories-list.php
        ├── featured.php
        ├── infobox.php          item infobox
        ├── story-list.php
        └── toc.php              table of contents
```

## Manifest

`theme.php` is the root of the chain: no `parent`, and no own css/js, because the
core `app.css` and JS are loaded by `site/layouts/header.php` directly:

```php
return [
    'name'   => 'wiki',
    'parent' => null,
    'css'    => [],
    'js'     => [],
];
```

> **Note.** Modify `wiki` files only when you want to change the default design itself.
> To build your own theme, copy it into `themes/{name}/` and override only the files
> you need (see "Themes" in the root README of the `themes` folder).

## Building a wiki-based theme

1. Create `resources/views/themes/{name}/theme.php` with `'parent' => 'wiki'`.
2. Put into `themes/{name}/site/...` only the files you want to change
   (e.g. `layouts/partials/header-body.php`, `pages/home.php`).
3. Everything else is inherited from `wiki` automatically.
4. Put your styles into `themes/{name}/assets/css/*.css` and list them in the
   `css[]` manifest key.
5. Set `'template' => '{name}'` in `config/general.php`.