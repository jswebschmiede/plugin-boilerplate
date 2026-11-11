<?php
/**
 * Template loader for files in views/.
 *
 * @package plugin-boilerplate
 */

declare( strict_types=1 );

namespace Jswebforge\PluginBoilerplate\Common;

\defined( 'ABSPATH' ) || exit;

/**
 * Resolves and renders view templates.
 *
 * Templates receive their data as local variables and are responsible for
 * escaping everything they output.
 */
final class View {
	/**
	 * Outputs a template.
	 *
	 * @param string               $template Template path relative to views/, without .php.
	 * @param array<string, mixed> $data     Variables made available to the template.
	 * @return void
	 */
	public static function display( string $template, array $data = array() ): void {
		$path = self::get_path( $template );

		if ( '' === $path ) {
			return;
		}

		( static function ( string $__path, array $__data ): void {
			// phpcs:ignore WordPress.PHP.DontExtract.extract_extract -- Keys are fixed by the calling code, not user input.
			extract( $__data, EXTR_SKIP );
			require $__path;
		} )( $path, $data );
	}

	/**
	 * Returns a rendered template as a string, e.g. for shortcodes.
	 *
	 * @param string               $template Template path relative to views/, without .php.
	 * @param array<string, mixed> $data     Variables made available to the template.
	 * @return string Rendered markup.
	 */
	public static function render( string $template, array $data = array() ): string {
		ob_start();
		self::display( $template, $data );

		return (string) ob_get_clean();
	}

	/**
	 * Resolves a template to an absolute path.
	 *
	 * @param string $template Template path relative to views/, without .php.
	 * @return string Absolute path, or an empty string when the template name is invalid or missing.
	 */
	public static function get_path( string $template ): string {
		// Only lowercase names and sub directories; no dots, so no traversal.
		if ( 1 !== preg_match( '/^[a-z0-9\/-]+$/', $template ) ) {
			return '';
		}

		$path = plugin_dir_path( PLUGIN_BOILERPLATE_PLUGIN_FILE ) . 'views/' . $template . '.php';

		return is_readable( $path ) ? $path : '';
	}
}
