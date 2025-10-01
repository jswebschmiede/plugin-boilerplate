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
 * Domain Path: /languages
 */

defined('ABSPATH') or die('Thanks for visting');

use JS\PluginBoilerplate\PluginBoilerplate;

require_once(plugin_dir_path(__FILE__) . '/vendor/autoload.php');

$pluginBoilerplate = PluginBoilerplate::get_instance();
$pluginBoilerplate->run();

register_activation_hook(__FILE__, [$pluginBoilerplate, 'activate']);
register_deactivation_hook(__FILE__, [$pluginBoilerplate, 'deactivate']);

