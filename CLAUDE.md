# Plugin Boilerplate

WordPress plugin template (PHP 8.3, WP 7.0+). `bin/setup.php` turns it into a new plugin by token replacement. README.md covers structure, commands and the included examples.

## Read first

- Settings (adding, reading, sanitizing fields, new field types): `docs/settings.md`.

## Tokens

`bin/setup.php` renames everything by plain text replacement. Write names only in these exact forms so a generated plugin has no leftovers:

| Token                          | Use                                                                       |
| ------------------------------ | ------------------------------------------------------------------------- |
| `plugin-boilerplate`           | Slug, text domain, handles, file names                                    |
| `plugin_boilerplate`           | Option names, hooks, shortcode, meta keys                                 |
| `PLUGIN_BOILERPLATE`           | Constants                                                                 |
| `Jswebforge\PluginBoilerplate` | Namespace                                                                 |
| `pb` + `:` `_` `)`             | Tailwind prefix (`pb:flex`), post type and taxonomy prefix (`pb_example`) |

## Conventions

- WPCS `WordPress-Core`: long `array()`, spaces inside parentheses, first-class callables as `foo( ... )` (Rector writes `foo(...)`; phpcs rejects it).
- Services implement `Hookable`; `init()` only adds hooks. Register them in `Plugin::get_services()`, admin-only ones in the `is_admin()` branch.
- Views escape late; controllers sanitize input on read (`wp_unslash()` first). State-changing requests check nonce and capability.
- `uninstall.php` and `bin/playground-demo.php` run without the plugin loaded: option, post type and meta names are literals there. Keep them in sync with the class constants.
- Code, comments and UI strings in English. German lives in `languages/plugin-boilerplate-de_DE.po`.
- In wp-admin, core's unlayered CSS beats the layered Tailwind utilities: use the important modifier (`pb:mt-4!`) when a utility has no effect there.
- A new dev-only file or folder goes into `EXCLUDED_*` in `bin/package.php` and gets an `export-ignore` line in `.gitattributes`.

## Done means

1. `composer php:lint` reports 0 errors and `composer php:rector` changes nothing.
2. After JS or CSS changes: `pnpm run build`, `pnpm run lint:js`, `pnpm run lint:css` pass.
3. After changing a translatable string: `composer make-pot`, `vendor/bin/wp i18n update-po languages/plugin-boilerplate.pot languages`, and every new entry in the de_DE `.po` translated.
4. Runtime behaviour is checked in Playground, not assumed. One-off scripts (from the plugin directory, whose name must equal the slug):
    ```bash
    npx @wp-playground/cli@latest php --auto-mount --php=8.3 --mount=<script-dir>:/tests \
      --define-bool WP_DEBUG true --define-bool WP_DEBUG_LOG true -- /tests/<script>.php
    ```
    The script starts with `require '/wordpress/wp-load.php';`. `composer playground` serves the full site with demo content.
