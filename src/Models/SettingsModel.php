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
 * Reads, defaults and sanitizes the plugin settings.
 *
 * The three options demonstrate one field type each: text, select and checkbox list.
 * Option names are duplicated in uninstall.php; keep both in sync.
 */
class SettingsModel {
	/**
	 * Settings API option group.
	 */
	public const string OPTION_GROUP = 'plugin_boilerplate';

	/**
	 * Settings page menu slug.
	 */
	public const string MENU_SLUG = 'plugin-boilerplate';

	/**
	 * Option name of the greeting text (text field).
	 */
	public const string GREETING_OPTION = 'plugin_boilerplate_greeting';

	/**
	 * Option name of the shortcode layout (select field).
	 */
	public const string LAYOUT_OPTION = 'plugin_boilerplate_layout';

	/**
	 * Option name of the post types listed by the shortcode (checkbox list).
	 */
	public const string POST_TYPES_OPTION = 'plugin_boilerplate_post_types';

	/**
	 * Layout: one column.
	 */
	public const string LAYOUT_LIST = 'list';

	/**
	 * Layout: responsive grid.
	 */
	public const string LAYOUT_GRID = 'grid';

	/**
	 * Allowed layouts.
	 */
	public const array LAYOUTS = array( self::LAYOUT_LIST, self::LAYOUT_GRID );

	/**
	 * Post types used until the settings page is saved.
	 */
	public const array DEFAULT_POST_TYPES = array( 'post' );

	/**
	 * Gets the greeting text.
	 *
	 * @return string Plain text, may be empty.
	 */
	public static function get_greeting(): string {
		$greeting = get_option( self::GREETING_OPTION, '' );

		return is_string( $greeting ) ? sanitize_text_field( $greeting ) : '';
	}

	/**
	 * Gets the shortcode layout.
	 *
	 * @return string One of self::LAYOUTS.
	 */
	public static function get_layout(): string {
		return self::sanitize_layout( get_option( self::LAYOUT_OPTION, self::LAYOUT_LIST ) );
	}

	/**
	 * Gets the post types listed by the shortcode.
	 *
	 * @return string[] Registered, selectable post type names.
	 */
	public static function get_post_types(): array {
		return array_values(
			array_filter(
				self::get_list_option( self::POST_TYPES_OPTION, self::DEFAULT_POST_TYPES ),
				self::is_selectable_post_type( ... )
			)
		);
	}

	/**
	 * Gets layout choices.
	 *
	 * @return array<string, string> Layout => label.
	 */
	public static function get_layout_choices(): array {
		return array(
			self::LAYOUT_LIST => __( 'List', 'plugin-boilerplate' ),
			self::LAYOUT_GRID => __( 'Grid', 'plugin-boilerplate' ),
		);
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
	 * Sanitizes the greeting text.
	 *
	 * @param mixed $value Raw submitted value.
	 * @return string Plain text.
	 */
	public static function sanitize_greeting( mixed $value ): string {
		return is_string( $value ) ? sanitize_text_field( $value ) : '';
	}

	/**
	 * Sanitizes the layout.
	 *
	 * @param mixed $value Raw submitted value.
	 * @return string One of self::LAYOUTS.
	 */
	public static function sanitize_layout( mixed $value ): string {
		return is_string( $value ) && in_array( $value, self::LAYOUTS, true ) ? $value : self::LAYOUT_LIST;
	}

	/**
	 * Sanitizes the post types.
	 *
	 * @param mixed $value Raw submitted value.
	 * @return string[] Selectable post type names.
	 */
	public static function sanitize_post_types( mixed $value ): array {
		return array_values(
			array_filter(
				self::sanitize_list( $value ),
				self::is_selectable_post_type( ... )
			)
		);
	}

	/**
	 * Gets a stored list option.
	 *
	 * A missing option uses the defaults. A saved empty list stays empty.
	 *
	 * @param string   $name           Option name.
	 * @param string[] $default_values Values used until the settings page is saved.
	 * @return string[] Sanitized, unique keys.
	 */
	private static function get_list_option( string $name, array $default_values ): array {
		$value = get_option( $name, false );

		if ( false === $value ) {
			$value = $default_values;
		}

		return self::sanitize_list( $value );
	}

	/**
	 * Normalizes a submitted or stored list of keys.
	 *
	 * @param mixed $value Raw list.
	 * @return string[] Unique sanitized keys. Empty strings are dropped.
	 */
	private static function sanitize_list( mixed $value ): array {
		if ( ! is_array( $value ) ) {
			return array();
		}

		$sanitized = array();

		foreach ( $value as $item ) {
			if ( ! is_string( $item ) || '' === $item ) {
				continue;
			}

			$sanitized[] = sanitize_key( $item );
		}

		return array_values( array_unique( $sanitized ) );
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
