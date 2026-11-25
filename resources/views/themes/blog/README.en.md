# "blog" theme

A blog theme built on top of the base `wiki` theme. It demonstrates overriding the
home and article layout without touching the core or controllers.

## What differs from wiki

| Area | wiki | blog |
|---|---|---|
| Header | full header + tabs | lightweight header + search, **pill menu** |
| Home | sections stacked in one column | **two-column grid**: post feed + right sidebar |
| Hero post | small card on the right | **large "post of the day"** with a cover, full width |
| Sidebar | none | "popular", "categories", "did you know", statistics |
| Article | side TOC column | **narrow column** + cover on top, related as a feed |

## Folder structure

```
resources/views/themes/blog/
├── theme.php                          manifest (parent = wiki)
├── assets/css/blog.css                blog styles (loaded after app.css)
└── site/
    ├── layouts/
    │   ├── article.php                blog article layout (overrides wiki)
    │   └── partials/header-body.php   blog header + pill menu
    └── pages/
        └── home.php                   blog home with sidebar
```

The blog **does not copy** the skeleton: `header.php`, `footer.php`, components, and
the listing pages are inherited from `wiki` through the `blog → wiki` chain.

## Manifest

```php
return [
    'name'   => 'blog',
    'parent' => 'wiki',
    'css'    => ['assets/css/blog.css'],
    'js'     => [],
];
```

## Enabling

In `config/general.php`:

```php
'template' => 'blog',   // or 'wiki' to return to the default
```

After switching themes, do a **hard refresh (Ctrl+F5)**: the version number in the
URL (`?108`) does not change when a theme changes, so the browser may keep a cache.

## How it works

- The `<body>` gets the active theme class (`<body class="blog">`) from the shared
  `wiki/site/layouts/header.php`, and all blog CSS rules are scoped under `body.blog`.
- `pages/home.php`, `layouts/article.php` and `partials/header-body.php` exist **only**
  in the blog; when `template=blog` the resolver returns them, everything else comes
  from `wiki`.
- `blog.css` is copied into `public/assets/` (`Theme::publishAssets()`) — called on
  render and during the static build.

## Customization

To tune the blog:

- **Colors / layout** — edit `assets/css/blog.css` (use `var(--*)` tokens from app.css).
- **Header** — `site/layouts/partials/header-body.php`.
- **Home** — `site/pages/home.php` (the available data is listed below).
- **Article** — `site/layouts/article.php`.

### Data available in the blog home (`home.php`)

All are passed from the controller unchanged:

| Variable | Contents |
|---|---|
| `$featuredBig` / `$bestFacts` | lead/featured post |
| `$items` | recent entries |
| `$didYouKnow` | "did you know" block |
| `$sections` | category tree (roots with children) |
| `$popular` | popular entries |
| `$stats` | counters: `facts`, `articles`, `sites`, `categories` |
| `$mod` | `static` \| `preview` |

## Requirements

- Works with the static build and the admin preview.
- No controller or database-schema changes required.