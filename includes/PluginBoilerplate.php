<?php

/**
 * Main plugin class.
 */

namespace JS\PluginBoilerplate;

defined( 'ABSPATH' ) or die( 'Thanks for visting' );

use Carbon_Fields\Carbon_Fields;
use JS\PluginBoilerplate\Assets\Assets;
use JS\PluginBoilerplate\Shortcodes\Shortcode;
use JS\PluginBoilerplate\Backend\AdminSettings;
use JS\PluginBoilerplate\Backend\ExamplePostType;

class PluginBoilerplate {
	/**
	 * The single instance of the class.
	 *
	 * @var PluginBoilerplate|null
	 */
	private static ?PluginBoilerplate $_instance = null;

	/**
	 * Prevent direct instantiation.
	 */
	private function __construct() {
	}

	/**
	 * Prevent cloning of the instance.
	 */
	private function __clone(): void {
	}

	/**
	 * Prevent unserialization of the instance.
	 */
	public function __wakeup(): void {
		throw new \Exception( 'Cannot unserialize singleton' );
	}

	/**
	 * Get the singleton instance.
	 *
	 * @return PluginBoilerplate
	 */
	public static function get_instance(): PluginBoilerplate {
		if ( null === self::$_instance ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Boot Carbon Fields early.
	 */
	public function boot_carbon_fields(): void {
		Carbon_Fields::boot();
	}

	/**
	 * Run the plugin.
	 */
	public function run(): void {
		// Boot Carbon Fields early, before the 'init' action
		add_action( 'after_setup_theme', array( $this, 'boot_carbon_fields' ) );

		// Initialize the plugin immediately
		$this->init();
	}

	/**
	 * Initializes the plugin.
	 */
	public function init(): void {
		// Load plugin textdomain for translations
		load_plugin_textdomain(
			'plugin-boilerplate',
			false,
			plugin_basename( __DIR__ ) . '/../languages',
		);

		$assets = new Assets();
		$assets->init();

		$shortcode = new Shortcode();
		$shortcode->init();

		$admin_settings = new AdminSettings();
		$admin_settings->init();

		$example_post_type = new ExamplePostType();
		$example_post_type->init();
	}

	/**
	 * Activates the plugin.
	 */
	public function activate(): void {
		flush_rewrite_rules();
	}

	/**
	 * Deactivates the plugin.
	 */
	public function deactivate(): void {
		flush_rewrite_rules();
	}
}
