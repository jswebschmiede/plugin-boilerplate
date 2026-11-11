# Plugin Boilerplate

Starting point for WordPress plugins

<!-- setup:start -->

## Creating a new plugin

This repository is a template. `bin/setup.php` turns it into a new plugin: it replaces every placeholder token, renames the files and then deletes itself. This section is removed as well.

```bash
git clone https://github.com/jswebschmiede/plugin-boilerplate.git my-plugin
cd my-plugin
rm -rf .git && git init

php bin/setup.php                       # interactive
php bin/setup.php --name="My Plugin" --no-interaction --dry-run   # preview only
```

Run `php bin/setup.php --help` for all options. The defaults are derived from the plugin name.

| Token                                | Example replacement | Used for                                         |
| ------------------------------------ | ------------------- | ------------------------------------------------ |
| `Plugin Boilerplate`                 | `My Plugin`         | Display name                                     |
| `plugin-boilerplate`                 | `my-plugin`         | Slug, text domain, file names, handles           |
| `plugin_boilerplate`                 | `my_plugin`         | Option names, shortcode tag, hook names          |
| `PLUGIN_BOILERPLATE`                 | `MY_PLUGIN`         | Constants                                        |
| `Jswebforge\PluginBoilerplate`       | `Acme\MyPlugin`     | PHP namespace                                    |
| `jswebforge/plugin-boilerplate`      | `acme/my-plugin`    | Composer package                                 |
| `jswebschmiede/plugin-boilerplate`   | `acme/my-plugin`    | GitHub repository for updates                    |
| short prefix                         | `mp`                | Post type, taxonomy, Tailwind prefix (`mp:flex`) |
| `Author`, `Author URI` header fields | your values         | Plugin header and `composer.json`                |

Afterwards:

```bash
composer update --lock && composer install
pnpm install && pnpm run build
composer php:lint
```

<!-- setup:end -->

## Requirements

- PHP 8.3+ and Composer
- WordPress 7.0+
- Node.js 20.18+ and pnpm for the asset build and the local Playground site

## Structure

```
plugin-boilerplate.php      Plugin header, constants, autoloader check, bootstrap
uninstall.php               Removes the settings option (multisite aware)
src/Common/                 Infrastructure: Plugin (service list), Hookable, View, Assets, Updater, Lifecycle
src/Controllers/            Hook wiring and request handling
src/Models/                 Data access and sanitizing
views/                      Templates; escape everything you output here
assets/js, assets/css       Sources for the wp-scripts build
build/                      Compiled assets (generated, not committed)
languages/                  .pot, .po; .mo/.json/.l10n.php are generated
bin/package.php             Builds the release zip
bin/playground-demo.php     Demo content for composer playground (blueprint.json)
docs/                       Developer documentation (not shipped in the zip)
```

Classes live under `Jswebforge\PluginBoilerplate\` (Composer PSR-4 from `src/`).

## Included examples

| Module                    | Class                                 | What it shows                                                                                                    |
| ------------------------- | ------------------------------------- | ---------------------------------------------------------------------------------------------------------------- |
| Settings page             | `SettingsController`, `SettingsModel` | Settings API page under **Settings → Plugin Boilerplate**; all fields stored in one option, defined in one array |
| Custom post type          | `PostTypeController`                  | Post type `pb_example`, taxonomy `pb_example_type`, registered meta, list column, taxonomy filter                |
| Admin action              | `ToggleController`                    | Nonce-protected row action (`admin.php?action=…`) that toggles a meta flag, with notice and redirect             |
| Shortcode                 | `ShortcodeController`                 | `[plugin_boilerplate count="5"]` renders a view and enqueues the frontend assets on demand                       |
| GitHub updates            | `Updater`                             | Offers new versions from GitHub releases via the `Update URI` header                                             |
| Activation / deactivation | `Lifecycle`                           | Registers the post type before flushing rewrite rules                                                            |

Delete what you don't need: remove the class, its entry in `Plugin::get_services()` and its views.

### Add a setting

Add one array entry to `SettingsModel::get_fields()` and read it with `SettingsModel::get( 'key' )`. Field types: `text`, `textarea`, `select`, `radio`, `checkbox`, `checkbox-list`. See [docs/settings.md](docs/settings.md) for all keys, examples, custom sanitizing and new field types.

## Local test site

`composer playground` starts WordPress Playground with this directory mounted as the plugin. `blueprint.json` turns on `WP_DEBUG` and `WP_DEBUG_LOG` and runs `bin/playground-demo.php`, which activates the plugin, saves example settings and creates three example entries (one featured) and the page `/plugin-boilerplate-demo/` with the shortcode.

Playground keeps the site between starts; the demo script only runs while that page does not exist. To start from scratch:

```bash
npx @wp-playground/cli@latest start --php=8.3 --blueprint=blueprint.json --reset
```

## Conventions

- Every PHP file starts with a file docblock, `declare( strict_types=1 );` and `\defined( 'ABSPATH' ) || exit;`.
- Services implement `Hookable`; `init()` only registers hooks. Add new services in `Plugin::get_services()`, admin-only services inside the `is_admin()` branch.
- Templates are rendered with `View::display( 'name', $data )` or `View::render()` for shortcodes. Templates escape late (`esc_html`, `esc_attr`, `esc_url`).
- Input is read with `wp_unslash()` and sanitized immediately; state-changing requests check a nonce and a capability.
- WordPress Coding Standards (`WordPress-Core`), long array syntax, typed class constants.

## Assets and Tailwind

`webpack.config.js` defines the entries `admin` and `frontend`. Each produces `build/<entry>.js`, `.css`, `-rtl.css` and `.asset.php`. `src/Common/Assets.php` registers them; the admin entry loads only on the plugin's screens, the frontend entry is enqueued by the shortcode.

Tailwind CSS 4 runs through PostCSS (`postcss.config.js`). Preflight is disabled so theme and wp-admin base styles stay untouched, and all utilities use a prefix, e.g. `pb:flex`, `pb:hover:underline`, `pb:sm:grid-cols-2`. Only `views/`, `src/` and `assets/js/` are scanned for class names. wp-admin CSS is not layered and wins over layered utilities; use the important modifier (`pb:mt-4!`) where needed.

## Translations

UI strings are English; `languages/plugin-boilerplate-de_DE.po` contains the German translation.

```bash
composer i18n      # build assets, update .pot/.po, compile .mo, .json (JS) and .l10n.php
```

JavaScript strings are extracted from `build/`, so the JSON files match the script handles registered by `Assets`.

## Commands

| Command                     | Purpose                                                                                      |
| --------------------------- | -------------------------------------------------------------------------------------------- |
| `pnpm run start`            | Watch and rebuild assets                                                                     |
| `pnpm run build`            | Production asset build                                                                       |
| `pnpm run lint:js`          | ESLint (WordPress config)                                                                    |
| `pnpm run lint:css`         | Stylelint (WordPress config plus Tailwind at-rules)                                          |
| `composer php:lint`         | PHPCS with WPCS and PHPCompatibility                                                         |
| `composer php:lint:autofix` | PHPCBF                                                                                       |
| `composer php:rector`       | Rector dry run (PHP 8.3 set)                                                                 |
| `composer playground`       | Local WordPress Playground with the plugin, demo content and `WP_DEBUG`; opens the demo page |
| `composer production`       | Lint, build, compile translations, package `dist/*.zip`                                      |

## Releases and updates

The `Update URI` header points at GitHub, so WordPress asks `Updater` instead of wordpress.org. `Updater` reads the latest release of `jswebschmiede/plugin-boilerplate` and offers the attached `plugin-boilerplate-<version>.zip`.

1. Raise `Version` in the plugin header (the only place the version is stored).
2. `composer production`
3. `git tag -a vX.Y.Z -m "vX.Y.Z" && git push origin master vX.Y.Z`
4. `gh release create vX.Y.Z dist/plugin-boilerplate-X.Y.Z.zip --title "vX.Y.Z" --generate-notes`

## License

GPL-2.0-or-later
