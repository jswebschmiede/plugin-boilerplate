# Settings

The settings page under **Settings → Plugin Boilerplate** is built with the WordPress Settings API. Every field is defined once, as an array entry. Registration, rendering, defaults, sanitizing and uninstall are generic.

| File                                     | Role                                                                  |
| ---------------------------------------- | --------------------------------------------------------------------- |
| `src/Models/SettingsModel.php`           | Field definitions, `get()`, `sanitize()`, sanitizing per type         |
| `src/Controllers/SettingsController.php` | Options page, `register_setting()`, one settings field per definition |
| `views/fields/<type>.php`                | Markup of one field type                                              |
| `views/admin-settings.php`               | Page wrapper with the form                                            |
| `uninstall.php`                          | Deletes the option                                                    |

All values are stored in **one option**, `plugin_boilerplate_settings`, as an associative array:

```php
array(
	'greeting'   => 'Hello',
	'intro'      => "Line one\nLine two",
	'layout'     => 'grid',
	'order'      => 'date',
	'show_date'  => true,
	'post_types' => array( 'post', 'page' ),
)
```

## Reading a setting

```php
use Jswebforge\PluginBoilerplate\Models\SettingsModel;

$layout     = (string) SettingsModel::get( 'layout' );
$show_date  = (bool) SettingsModel::get( 'show_date' );
$post_types = (array) SettingsModel::get( 'post_types' );
```

- The value is already sanitized. The cast only tells PHP and your IDE the type; the [type table](#field-types) lists what each type returns.
- Until the settings page has been saved, or for a field added after the last save, `get()` returns the field's `default`.
- Stored values are validated again on every read. A choice that no longer exists, such as an unregistered post type, is dropped.
- An unknown key returns `null` and triggers a `_doing_it_wrong()` notice when `WP_DEBUG` is on.
- Call `get()` on `init` or later. Labels are translated and the post type choices need registered post types.

## Adding a field

Add an entry to the array in `SettingsModel::get_fields()`. The array key is the setting key; use lowercase letters, digits and underscores.

```php
'show_excerpt' => array(
	'type'        => 'checkbox',
	'label'       => __( 'Excerpt', 'plugin-boilerplate' ),
	'description' => __( 'Show the excerpt of each entry.', 'plugin-boilerplate' ),
	'default'     => false,
),
```

That is all the PHP needed. The field appears on the settings page, is saved and sanitized, and `SettingsModel::get( 'show_excerpt' )` returns it. Fields appear in the order of the array.

Then update the translations:

```bash
composer make-pot
vendor/bin/wp i18n update-po languages/plugin-boilerplate.pot languages
# translate the new strings in languages/plugin-boilerplate-de_DE.po
```

`composer i18n` runs the whole chain including `.mo`, `.json` and `.l10n.php`.

### Definition keys

| Key           | Required                           | Meaning                                                                                    |
| ------------- | ---------------------------------- | ------------------------------------------------------------------------------------------ |
| `type`        | yes                                | One of the [field types](#field-types). Selects the view and the sanitizing.               |
| `label`       | yes                                | Row title. For grouped types it is also the screen reader legend.                          |
| `default`     | yes                                | Value until the field is saved. Use the type's value type (`''`, `false`, `array()`, …).   |
| `description` | no                                 | Help text below the input. For `checkbox` it is the text next to the box.                  |
| `choices`     | `select`, `radio`, `checkbox-list` | `value => label`. Values are what gets stored.                                             |
| `sanitize`    | no                                 | Callable that replaces the type's sanitizing, see [Custom sanitizing](#custom-sanitizing). |

You can add more keys for your own types; views receive the whole definition as `$field`.

## Field types

| Type            | Stored value | Input                       | Sanitizing                                                    |
| --------------- | ------------ | --------------------------- | ------------------------------------------------------------- |
| `text`          | `string`     | `<input type="text">`       | `sanitize_text_field()`; non-strings become the default       |
| `textarea`      | `string`     | `<textarea>`                | `sanitize_textarea_field()`; keeps line breaks                |
| `select`        | `string`     | `<select>`                  | Must be a key of `choices`, else the default                  |
| `radio`         | `string`     | Radio buttons in a fieldset | Must be a key of `choices`, else the default                  |
| `checkbox`      | `bool`       | One checkbox in a fieldset  | `true` only for `'1'`; unchecked is `false`                   |
| `checkbox-list` | `string[]`   | Checkboxes in a fieldset    | Only keys of `choices`, duplicates removed; none is `array()` |

### Examples

```php
'title' => array(
	'type'    => 'text',
	'label'   => __( 'Title', 'plugin-boilerplate' ),
	'default' => '',
),

'notes' => array(
	'type'        => 'textarea',
	'label'       => __( 'Notes', 'plugin-boilerplate' ),
	'description' => __( 'Plain text. Line breaks are kept.', 'plugin-boilerplate' ),
	'default'     => '',
),

'size' => array(
	'type'    => 'select',
	'label'   => __( 'Size', 'plugin-boilerplate' ),
	'choices' => array(
		'small' => __( 'Small', 'plugin-boilerplate' ),
		'large' => __( 'Large', 'plugin-boilerplate' ),
	),
	'default' => 'small',
),

'position' => array(
	'type'    => 'radio',
	'label'   => __( 'Position', 'plugin-boilerplate' ),
	'choices' => array(
		'before' => __( 'Before the content', 'plugin-boilerplate' ),
		'after'  => __( 'After the content', 'plugin-boilerplate' ),
	),
	'default' => 'after',
),

'enabled' => array(
	'type'        => 'checkbox',
	'label'       => __( 'Status', 'plugin-boilerplate' ),
	'description' => __( 'Enable the output', 'plugin-boilerplate' ),
	'default'     => true,
),

'roles' => array(
	'type'    => 'checkbox-list',
	'label'   => __( 'Roles', 'plugin-boilerplate' ),
	'choices' => wp_roles()->get_names(),
	'default' => array( 'administrator' ),
),
```

`choices` can be computed, as `post_types` does with `SettingsModel::get_post_type_choices()`. They are evaluated on every `get()` and on save, so keep them cheap.

### Outputting values

Values are sanitized, not escaped. Escape them where you output them:

```php
echo esc_html( (string) SettingsModel::get( 'title' ) );
echo wp_kses_post( wpautop( esc_html( (string) SettingsModel::get( 'notes' ) ) ) ); // textarea with paragraphs
```

## Custom sanitizing

A `sanitize` callable replaces the type's rule. It receives the raw value. That value is `null` when the field was not submitted, and it is also the stored value or the default when read through `get()`. The callable must therefore:

- handle `null` and wrong types,
- return the same value when it gets its own output again (WordPress runs the sanitize callback twice when the option is first created).

```php
'accent_color' => array(
	'type'     => 'text',
	'label'    => __( 'Accent color', 'plugin-boilerplate' ),
	'default'  => '#2271b1',
	'sanitize' => static fn( mixed $value ): string => is_string( $value ) && sanitize_hex_color( $value ) ? $value : '#2271b1',
),
```

To show an error message on the settings page, call `add_settings_error( SettingsModel::OPTION_NAME, 'code', $message )` inside the callable.

## Adding a field type

As an example, a `number` type with `min` and `max`:

**1. Sanitizing**: add a branch to the `match` in `SettingsModel::sanitize_field()`:

```php
'number' => is_numeric( $value )
	? max( (int) ( $field['min'] ?? PHP_INT_MIN ), min( (int) ( $field['max'] ?? PHP_INT_MAX ), (int) $value ) )
	: $default,
```

**2. View**: create `views/fields/number.php`. Every view receives these variables:

| Variable       | Content                                                     |
| -------------- | ----------------------------------------------------------- |
| `$id`          | `plugin_boilerplate_settings-<key>`; matches the row label  |
| `$name`        | `plugin_boilerplate_settings[<key>]`; append `[]` for lists |
| `$value`       | Current value from `SettingsModel::get()`                   |
| `$label`       | Field label                                                 |
| `$description` | Description or `''`                                         |
| `$choices`     | Choices or `array()`                                        |
| `$field`       | The complete definition, for keys like `min`/`max`          |

```php
<?php
/**
 * Number settings field.
 *
 * @package plugin-boilerplate
 */

declare( strict_types=1 );

\defined( 'ABSPATH' ) || exit;

$id          ??= '';
$name        ??= '';
$value       ??= 0;
$description ??= '';
$field       ??= array();
?>
<input
	type="number"
	class="small-text"
	id="<?php echo esc_attr( $id ); ?>"
	name="<?php echo esc_attr( $name ); ?>"
	value="<?php echo esc_attr( (string) $value ); ?>"
	<?php echo isset( $field['min'] ) ? 'min="' . esc_attr( (string) $field['min'] ) . '"' : ''; ?>
	<?php echo isset( $field['max'] ) ? 'max="' . esc_attr( (string) $field['max'] ) . '"' : ''; ?>
/>
<?php if ( '' !== $description ) : ?>
	<p class="description"><?php echo esc_html( $description ); ?></p>
<?php endif; ?>
```

**3. Grouped inputs only**: if the type renders several inputs, as radio buttons do, add it to `SettingsController::GROUPED_TYPES`. Its row then gets no `<label for>`, and the view must wrap the inputs in a `<fieldset>` with `<legend class="screen-reader-text">`. A type with one input, like `number`, needs no change here.

**4. Use it**:

```php
'per_page' => array(
	'type'    => 'number',
	'label'   => __( 'Entries per page', 'plugin-boilerplate' ),
	'min'     => 1,
	'max'     => 50,
	'default' => 10,
),
```

Template names may only contain lowercase letters, digits, `-` and `/` (see `View::get_path()`), so name types accordingly.

## Removing a field

Delete its entry from `get_fields()` and every `get()` call for it. The stored value is dropped the next time the settings are saved, because `sanitize()` only keeps defined keys. `uninstall.php` needs no change.

## Limits

- All fields render in one section (`plugin_boilerplate_main`) on one page. For more sections, add a `section` key to the definitions, register the sections in `SettingsController::register_settings()` and pass the key to `add_settings_field()`.
- `sanitize()` treats a missing key as an empty submission. When you write a single setting from code, pass all current values, otherwise checkboxes and checkbox lists that are not in the array are stored as `false` and `array()`:

    ```php
    $settings = array();
    foreach ( array_keys( SettingsModel::get_fields() ) as $key ) {
    	$settings[ $key ] = SettingsModel::get( $key );
    }
    $settings['show_date'] = false;
    update_option( SettingsModel::OPTION_NAME, $settings );
    ```

- The option is not exposed in the REST API (`show_in_rest` is not set).
