<?php
/**
 * Script and style registration for the wp-scripts build.
 *
 * @package plugin-boilerplate
 */

declare( strict_types=1 );

namespace Jswebforge\PluginBoilerplate\Common;

use Jswebforge\PluginBoilerplate\Controllers\PostTypeController;
use Jswebforge\PluginBoilerplate\Models\SettingsModel;

\defined( 'ABSPATH' ) || exit;

/**
 * Registers the build/ entries and enqueues the admin entry on plugin screens.
 *
 * Every entry in webpack.config.js produces build/<entry>.js, build/<entry>.css
 * and build/<entry>.asset.php (dependencies and content hash).
 */
class Assets implements Hookable {
	/**
	 * Handle of the admin script and style.
	 */
	public const string ADMIN_HANDLE = 'plugin-boilerplate-admin';

	/**
	 * Handle of the frontend script and style.
	 */
	public const string FRONTEND_HANDLE = 'plugin-boilerplate-frontend';

	/**
	 * Registers asset hooks.
	 *
	 * @return void
	 */
	public function init(): void {
		add_action( 'wp_enqueue_scripts', array( $this, 'register_frontend' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin' ) );
	}

	/**
	 * Registers the frontend entry. Components enqueue it when they render.
	 *
	 * @return void
	 */
	public function register_frontend(): void {
		$this->register_entry( 'frontend', self::FRONTEND_HANDLE );
	}

	/**
	 * Enqueues the admin entry on the plugin's own screens only.
	 *
	 * @return void
	 */
	public function enqueue_admin(): void {
		$screen = get_current_screen();

		if ( ! $screen instanceof \WP_Screen || ! in_array( $screen->id, self::get_admin_screen_ids(), true ) ) {
			return;
		}

		if ( $this->register_entry( 'admin', self::ADMIN_HANDLE ) ) {
			wp_enqueue_script( self::ADMIN_HANDLE );
			wp_enqueue_style( self::ADMIN_HANDLE );
		}
	}

	/**
	 * Enqueues the frontend entry. Safe to call while rendering content.
	 *
	 * @return void
	 */
	public static function enqueue_frontend(): void {
		wp_enqueue_script( self::FRONTEND_HANDLE );
		wp_enqueue_style( self::FRONTEND_HANDLE );
	}

	/**
	 * Admin screen IDs that load the admin entry.
	 *
	 * @return string[]
	 */
	private static function get_admin_screen_ids(): array {
		return array(
			'settings_page_' . SettingsModel::MENU_SLUG,
			PostTypeController::POST_TYPE,
			'edit-' . PostTypeController::POST_TYPE,
		);
	}

	/**
	 * Registers the script and style of one build entry under the same handle.
	 *
	 * @param string $entry  Entry name from webpack.config.js.
	 * @param string $handle Script and style handle.
	 * @return bool False when the entry has not been built yet.
	 */
	private function register_entry( string $entry, string $handle ): bool {
		$build_dir  = plugin_dir_path( PLUGIN_BOILERPLATE_PLUGIN_FILE ) . 'build/';
		$build_url  = plugin_dir_url( PLUGIN_BOILERPLATE_PLUGIN_FILE ) . 'build/';
		$asset_file = $build_dir . $entry . '.asset.php';

		if ( ! is_readable( $asset_file ) ) {
			return false;
		}

		$asset = require $asset_file;

		$dependencies = is_array( $asset['dependencies'] ?? null ) ? $asset['dependencies'] : array();
		$version      = is_string( $asset['version'] ?? null ) ? $asset['version'] : PLUGIN_BOILERPLATE_PLUGIN_VERSION;

		if ( is_readable( $build_dir . $entry . '.js' ) ) {
			wp_register_script(
				$handle,
				$build_url . $entry . '.js',
				$dependencies,
				$version,
				array(
					'in_footer' => true,
					'strategy'  => 'defer',
				)
			);
			wp_set_script_translations( $handle, Plugin::TEXT_DOMAIN, plugin_dir_path( PLUGIN_BOILERPLATE_PLUGIN_FILE ) . 'languages' );
		}

		if ( is_readable( $build_dir . $entry . '.css' ) ) {
			wp_register_style( $handle, $build_url . $entry . '.css', array(), $version );
			// wp-scripts also emits <entry>-rtl.css; WordPress swaps it in for RTL locales.
			wp_style_add_data( $handle, 'rtl', 'replace' );
		}

		return true;
	}
}
