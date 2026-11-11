<?php
/**
 * Plugin bootstrap.
 *
 * @package plugin-boilerplate
 */

declare( strict_types=1 );

namespace Jswebforge\PluginBoilerplate\Common;

use Jswebforge\PluginBoilerplate\Controllers\PostTypeController;
use Jswebforge\PluginBoilerplate\Controllers\SettingsController;
use Jswebforge\PluginBoilerplate\Controllers\ShortcodeController;
use Jswebforge\PluginBoilerplate\Controllers\ToggleController;

\defined( 'ABSPATH' ) || exit;

/**
 * Wires all services into WordPress.
 */
class Plugin {
	/**
	 * Plugin text domain.
	 */
	public const string TEXT_DOMAIN = 'plugin-boilerplate';

	/**
	 * Single instance.
	 */
	private static ?self $instance = null;

	/**
	 * Prevents direct instantiation.
	 */
	private function __construct() {
	}

	/**
	 * Prevents cloning.
	 *
	 * @return void
	 */
	private function __clone(): void {
	}

	/**
	 * Prevents unserializing.
	 *
	 * @throws \Exception Always.
	 * @return void
	 */
	public function __wakeup(): void {
		throw new \Exception( 'Cannot unserialize singleton' );
	}

	/**
	 * Returns the single instance.
	 *
	 * @return self
	 */
	public static function get_instance(): self {
		self::$instance ??= new self();

		return self::$instance;
	}

	/**
	 * Registers the text domain and all services.
	 *
	 * @return void
	 */
	public function run(): void {
		add_action( 'init', array( $this, 'load_textdomain' ), 0 );

		foreach ( $this->get_services() as $service ) {
			$service->init();
		}
	}

	/**
	 * Loads translations. Hooked to init because WordPress 6.7+ warns about earlier loading.
	 *
	 * @return void
	 */
	public function load_textdomain(): void {
		load_plugin_textdomain(
			self::TEXT_DOMAIN,
			false,
			dirname( plugin_basename( PLUGIN_BOILERPLATE_PLUGIN_FILE ) ) . '/languages'
		);
	}

	/**
	 * Returns the services for the current request.
	 *
	 * Add new services here. Admin-only services go into the is_admin() branch.
	 *
	 * @return Hookable[]
	 */
	private function get_services(): array {
		$services = array(
			// Runs outside of is_admin() because update checks also run via cron.
			new Updater(),
			new Assets(),
			new PostTypeController(),
			new ShortcodeController(),
		);

		if ( is_admin() ) {
			$services[] = new SettingsController();
			$services[] = new ToggleController();
		}

		return $services;
	}
}
