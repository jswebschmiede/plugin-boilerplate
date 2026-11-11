<?php
/**
 * Contract for classes that register WordPress hooks.
 *
 * @package plugin-boilerplate
 */

declare( strict_types=1 );

namespace Jswebforge\PluginBoilerplate\Common;

\defined( 'ABSPATH' ) || exit;

/**
 * A service that wires itself into WordPress through actions and filters.
 */
interface Hookable {
	/**
	 * Registers the service's actions and filters. Must not do any other work.
	 *
	 * @return void
	 */
	public function init(): void;
}
