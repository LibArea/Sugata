# Themes

The directory of themes for the public site. Each theme is a folder under
`resources/views/themes/`. The active theme is set in `config/general.php` via the
`'template'` key.

## Structure

```
resources/views/themes/
├── wiki/        default theme (chain root) — wiki
└── blog/        inherits wiki, overrides the home and article
```

## How it works

The **resolver** (`App\Content\Theme`) takes a path like `site/...` and looks it up
through the theme chain:

1. active theme → `themes/{template}/site/...`
2. its parent → `themes/{parent}/site/...`
3. … and so on up the `parent` links
4. emergency fallback → `themes/wiki/site/...`

If the file is found in none of them, the original path is returned and HLEB raises
a clear "file not found" error (an honest failure, not a silent crash).

The chain guards against duplicates and cycles. If the configured theme does not
exist, the resolver always falls back to `wiki`.

## Theme manifest

Every theme contains `theme.php`:

```php
return [
    'name'   => 'mytheme',              // name (matches the folder)
    'parent' => 'wiki',                 // parent theme (null = root)
    'css'    => ['assets/css/my.css'],  // extra css (loaded after base)
    'js'     => [],                     // extra js
];
```

- `parent`: the base theme. `wiki` is `null` (root).
- `css`/`js`: **add to** the core app.css/JS, they do not replace them. They are
  inherited from parents. Paths are relative to `public/`, so put files in
  `themes/{name}/assets/css/*.css` (URL `/assets/...`).

## Creating a theme

1. **Folder**: `resources/views/themes/{name}/`.
2. **Manifest**: `themes/{name}/theme.php` with `'parent' => 'wiki'`.
3. **Files**: put into `themes/{name}/site/...` only what you change. Layout notes:
   - `site/layouts/header.php` is the shared skeleton (`<head>`, CSS/JS loading).
     Prefer overriding `site/layouts/partials/header-body.php` (header body + nav).
   - `site/pages/home.php` — home page.
   - `site/layouts/article.php` — article/fact/site/page view.
   - everything else is inherited from `wiki`.
4. **Assets**: `themes/{name}/assets/css/*.css` (or `js/`), list them in `css[]`/`js[]`.
5. **Config**: `config/general.php` → `'template' => '{name}'`.

## Theme assets (css/js)

- The core `app.css` and JS are loaded by `wiki/site/layouts/header.php`.
- Theme css/js are loaded **after** the base ones (via `theme_assets()`), so cascade
  overrides base rules without `!important`.
- Theme files from `themes/{name}/assets/**` are copied into `public/assets/**`
  (`Theme::publishAssets()`). This runs on page render and on the static build
  (`BuildController::copyAssetsToStatic()`).
- The `<body>` gets the active theme class (`class="blog"`), so you can scope styles
  under `body.blog ...` and avoid conflicts with the admin panel.

## Switching the theme

`config/general.php`:

```php
'template' => 'wiki',   // or 'blog', or your theme name
```

After switching, do a hard refresh (Ctrl+F5): the asset version number in URLs does
not change when a theme changes, so the browser may keep a cache.

## Theme class — public methods

| Method | Purpose |
|---|---|
| `Theme::current()` | active theme name (falls back to wiki) |
| `Theme::manifest()` | manifest of the active theme |
| `Theme::chain()` | theme chain from the active up its parents |
| `Theme::resolve($path)` | real template path for `site/...` |
| `Theme::has($path)` | whether the file exists in the chain |
| `Theme::css()` / `Theme::js()` | theme asset list (inherited included) |
| `Theme::publishAssets()` | copies theme assets into `public/assets` |
| `Theme::themeExists($name)` | whether the theme folder exists |

## Integration

- `insert()` / `site_view()` in `app/helpers.php` run paths through
  `Theme::resolve()`.
- `theme_assets('css'|'js')` renders the `<link>`/`<script>` tags for theme assets.
- All points work identically for preview and the static build.

## IMPORTANT WARNINGS

- **Never delete** `themes/wiki` — it is the emergency fallback and the base that the
  resolver uses on any configuration error.
- A theme that overrides the **whole** `site/layouts/header.php` must itself output
  `<!DOCTYPE>`, `<head>` and load `app.css` + `theme_assets()`. Prefer overriding only
  `partials/header-body.php` (the skeleton is inherited).
- `shared/` and `admin/` are **not** themed — they belong to the shared core.