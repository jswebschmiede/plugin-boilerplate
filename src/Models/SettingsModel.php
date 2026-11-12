<?php
/**
 * Plugin settings stored with the WordPress Settings API.
 *
 * @package plugin-boilerplate
 */

declare( strict_types=1 );

namespace Jswebforge\PluginBoilerplate\Models;

\defined( 'ABSPATH' ) || exit;

/**
 * Defines, reads and sanitizes the plugin settings.
 *
 * All settings live in one option as an associative array. To add a setting,
 * add an entry to get_fields(); registration, rendering, defaults, sanitizing
 * and uninstall pick it up automatically.
 *
 * @phpstan-type Field array{
 *     type: 'text'|'textarea'|'select'|'radio'|'checkbox'|'checkbox-list',
 *     label: string,
 *     description?: string,
 *     choices?: array<string, string>,
 *     default: mixed,
 *     sanitize?: callable(mixed): mixed
 * }
 */
class SettingsModel {
	/**
	 * Option name. Also used as the Settings API option group.
	 */
	public const string OPTION_NAME = 'plugin_boilerplate_settings';

	/**
	 * Settings page menu slug.
	 */
	public const string MENU_SLUG = 'plugin-boilerplate';

	/**
	 * Field definitions, keyed by setting key.
	 *
	 * Keys per field:
	 * - type:        View in views/fields/ and sanitizing rule in sanitize_field().
	 * - label:       Row title on the settings page.
	 * - description: Help text (optional). For a checkbox it is the label next to the box.
	 * - choices:     Value => label, for select, radio and checkbox-list.
	 * - default:     Value used until the settings page is saved.
	 * - sanitize:    Optional callable that replaces the type's default sanitizing.
	 *
	 * @return array<string, array<string, mixed>>
	 * @phpstan-return array<string, Field>
	 */
	public static function get_fields(): array {
		return array(
			'greeting'   => array(
				'type'        => 'text',
				'label'       => __( 'Greeting', 'plugin-boilerplate' ),
				'description' => __( 'Shown above the list. Leave empty to hide it.', 'plugin-boilerplate' ),
				'default'     => '',
			),
			'intro'      => array(
				'type'        => 'textarea',
				'label'       => __( 'Introduction', 'plugin-boilerplate' ),
				'description' => __( 'Shown below the greeting. Line breaks are kept.', 'plugin-boilerplate' ),
				'default'     => '',
			),
			'layout'     => array(
				'type'        => 'select',
				'label'       => __( 'Layout', 'plugin-boilerplate' ),
				'description' => __( 'How the shortcode arranges the entries.', 'plugin-boilerplate' ),
				'choices'     => array(
					'list' => __( 'List', 'plugin-boilerplate' ),
					'grid' => __( 'Grid', 'plugin-boilerplate' ),
				),
				'default'     => 'list',
			),
			'order'      => array(
				'type'    => 'radio',
				'label'   => __( 'Order', 'plugin-boilerplate' ),
				'choices' => array(
					'date'  => __( 'Newest first', 'plugin-boilerplate' ),
					'title' => __( 'Alphabetical', 'plugin-boilerplate' ),
				),
				'default' => 'date',
			),
			'show_date'  => array(
				'type'        => 'checkbox',
				'label'       => __( 'Date', 'plugin-boilerplate' ),
				'description' => __( 'Show the publication date of each entry.', 'plugin-boilerplate' ),
				'default'     => true,
			),
			'post_types' => array(
				'type'        => 'checkbox-list',
				'label'       => __( 'Content types', 'plugin-boilerplate' ),
				'description' => __( 'The shortcode lists the latest entries of these content types.', 'plugin-boilerplate' ),
				'choices'     => self::get_post_type_choices(),
				'default'     => array( 'post' ),
			),
		);
	}

	/**
	 * Gets one setting.
	 *
	 * Falls back to the field default while the key has not been saved, so new
	 * fields work without a migration. Stored values are sanitized again, which
	 * drops choices that no longer exist (e.g. an unregistered post type).
	 *
	 * @param string $key Setting key from get_fields().
	 * @return mixed Sanitized value; null for an unknown key.
	 */
	public static function get( string $key ): mixed {
		$fields = self::get_fields();

		if ( ! isset( $fields[ $key ] ) ) {
			_doing_it_wrong(
				__METHOD__,
				/* translators: %s: Setting key. */
				esc_html( sprintf( __( 'Unknown setting "%s".', 'plugin-boilerplate' ), $key ) ),
				'0.1.0'
			);

			return null;
		}

		$stored = get_option( self::OPTION_NAME, array() );
		$value  = is_array( $stored ) && array_key_exists( $key, $stored ) ? $stored[ $key ] : $fields[ $key ]['default'];

		return self::sanitize_field( $fields[ $key ], $value );
	}

	/**
	 * Sanitizes the submitted settings. Used as the register_setting() callback.
	 *
	 * Unknown keys are dropped. A missing key counts as an empty submission, so
	 * unchecked checkboxes and empty checkbox lists are stored as false and an
	 * empty list. The result passes through unchanged when sanitized again.
	 *
	 * @param mixed $input Raw submitted option value.
	 * @return array<string, mixed> Sanitized settings.
	 */
	public static function sanitize( mixed $input ): array {
		$input     = is_array( $input ) ? $input : array();
		$sanitized = array();

		foreach ( self::get_fields() as $key => $field ) {
			$sanitized[ $key ] = self::sanitize_field( $field, $input[ $key ] ?? null );
		}

		return $sanitized;
	}

	/**
	 * Gets selectable post types for the settings checkboxes.
	 *
	 * @return array<string, string> Post type name => label.
	 */
	public static function get_post_type_choices(): array {
		$choices = array();

		foreach ( get_post_types( array( 'public' => true ), 'objects' ) as $post_type ) {
			if ( ! self::is_selectable_post_type( $post_type->name ) ) {
				continue;
			}

			$choices[ $post_type->name ] = sprintf( '%s (%s)', $post_type->labels->name, $post_type->name );
		}

		return $choices;
	}

	/**
	 * Sanitizes one value according to its field type.
	 *
	 * To add a field type, add a branch here and a view in views/fields/.
	 *
	 * @param array<string, mixed> $field Field definition.
	 * @param mixed                $value Raw value; null when it was not submitted.
	 * @return mixed Sanitized value.
	 */
	private static function sanitize_field( array $field, mixed $value ): mixed {
		if ( isset( $field['sanitize'] ) && is_callable( $field['sanitize'] ) ) {
			return $field['sanitize']( $value );
		}

		$default = $field['default'] ?? null;
		$choices = is_array( $field['choices'] ?? null ) ? $field['choices'] : array();

		return match ( $field['type'] ) {
			'text'            => is_string( $value ) ? sanitize_text_field( $value ) : $default,
			'textarea'        => is_string( $value ) ? sanitize_textarea_field( $value ) : $default,
			'select', 'radio' => is_string( $value ) && array_key_exists( $value, $choices ) ? $value : $default,
			'checkbox'        => in_array( $value, array( true, 1, '1' ), true ),
			'checkbox-list'   => self::sanitize_choice_list( $value, $choices ),
			default           => $default,
		};
	}

	/**
	 * Keeps the submitted values that exist in the choices.
	 *
	 * @param mixed                 $value   Raw list.
	 * @param array<string, string> $choices Value => label.
	 * @return string[] Unique values in submitted order.
	 */
	private static function sanitize_choice_list( mixed $value, array $choices ): array {
		if ( ! is_array( $value ) ) {
			return array();
		}

		$allowed  = array_map( strval( ... ), array_keys( $choices ) );
		$selected = array();

		foreach ( $value as $item ) {
			if ( is_string( $item ) && in_array( $item, $allowed, true ) ) {
				$selected[] = $item;
			}
		}

		return array_values( array_unique( $selected ) );
	}

	/**
	 * Whether a post type may be selected.
	 *
	 * Requires a registered public post type and excludes attachments and internal post types.
	 *
	 * @param string $post_type Post type name.
	 * @return bool
	 */
	private static function is_selectable_post_type( string $post_type ): bool {
		$post_type_object = get_post_type_object( $post_type );

		if ( ! $post_type_object || ! $post_type_object->public || 'attachment' === $post_type ) {
			return false;
		}

		return ! str_starts_with( $post_type, 'wp_' );
	}
}
