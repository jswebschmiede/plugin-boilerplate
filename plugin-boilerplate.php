<?php
/**
 * Plugin Name: Plugin Boilerplate
 * Description: Starting point for WordPress plugins
 * Version: 0.1.0
 * Requires at least: 7.0
 * Requires PHP: 8.3
 * Author: jswebforge
 * Author URI: https://jswebforge.de
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: plugin-boilerplate
 * Domain Path: /languages
 * Update URI: https://github.com/jswebschmiede/plugin-boilerplate
 *
 * @package plugin-boilerplate
 */

declare( strict_types=1 );

\defined( 'ABSPATH' ) || exit;

define( 'PLUGIN_BOILERPLATE_PLUGIN_FILE', __FILE__ );

$plugin_boilerplate_headers = get_file_data(
	__FILE__,
	array(
		'Version' => 'Version',
	),
	'plugin'
);

define( 'PLUGIN_BOILERPLATE_PLUGIN_VERSION', $plugin_boilerplate_headers['Version'] );

unset( $plugin_boilerplate_headers );

use Jswebforge\PluginBoilerplate\Common\Lifecycle;
use Jswebforge\PluginBoilerplate\Common\Plugin;

$plugin_boilerplate_autoload = plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

if ( ! file_exists( $plugin_boilerplate_autoload ) ) {
	add_action(
		'admin_notices',
		static function (): void {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			echo '<div class="notice notice-error"><p><strong>Plugin Boilerplate:</strong> ';
			echo esc_html__( 'Run composer install in the plugin directory to generate the autoloader.', 'plugin-boilerplate' );
			echo '</p></div>';
		}
	);
	return;
}

require_once $plugin_boilerplate_autoload;

unset( $plugin_boilerplate_autoload );

register_activation_hook( __FILE__, array( Lifecycle::class, 'activate' ) );
register_deactivation_hook( __FILE__, array( Lifecycle::class, 'deactivate' ) );

add_action(
	'plugins_loaded',
	static function (): void {
		Plugin::get_instance()->run();
	}
);
