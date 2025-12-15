<?php

/**
 * Plugin Name: Plugin Boilerplate
 * Description: Plugin Boilerplate
 * Version: 0.0.1
 * Author: Joerg Schoeneburg
 * Author URI: https://joerg-schoeneburg.de
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: plugin-boilerplate
 * Domain Path: /languages.
 */

defined( 'ABSPATH' ) or die( 'Thanks for visting' );

use JS\PluginBoilerplate\PluginBoilerplate;

if ( ! file_exists( plugin_dir_path( __FILE__ ) . '/vendor/autoload_packages.php' ) ) {
	add_action(
		'admin_notices',
		function () {
			echo '<div class="error"><p><strong>Plugin Boilerplate:</strong> Please run <code>composer install</code> in the plugin directory.</p></div>';
		}
	);
	return;
}

require_once plugin_dir_path( __FILE__ ) . '/vendor/autoload_packages.php';

$plugin_boilerplate = PluginBoilerplate::get_instance();
$plugin_boilerplate->run();

register_activation_hook( __FILE__, array( $plugin_boilerplate, 'activate' ) );
register_deactivation_hook( __FILE__, array( $plugin_boilerplate, 'deactivate' ) );
