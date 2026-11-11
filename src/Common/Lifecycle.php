<?php
/**
 * Activation and deactivation routines.
 *
 * @package plugin-boilerplate
 */

declare( strict_types=1 );

namespace Jswebforge\PluginBoilerplate\Common;

use Jswebforge\PluginBoilerplate\Controllers\PostTypeController;

\defined( 'ABSPATH' ) || exit;

/**
 * Keeps rewrite rules in sync with the plugin's post types.
 *
 * Activation runs before plugins_loaded hooks of this plugin, so the post type
 * must be registered here explicitly before flushing.
 */
final class Lifecycle {
	/**
	 * Registers content types and rebuilds rewrite rules.
	 *
	 * @return void
	 */
	public static function activate(): void {
		( new PostTypeController() )->register();
		flush_rewrite_rules();
	}

	/**
	 * Removes the plugin's rewrite rules.
	 *
	 * @return void
	 */
	public static function deactivate(): void {
		unregister_taxonomy( PostTypeController::TAXONOMY );
		unregister_post_type( PostTypeController::POST_TYPE );
		flush_rewrite_rules();
	}
}
