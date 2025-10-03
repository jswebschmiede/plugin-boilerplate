<?php

/**
 * The shortcode class.
 */

namespace JS\PluginBoilerplate\Shortcodes;

defined( 'ABSPATH' ) or die( 'Thanks for visting' );

/**
 * Shortcode class.
 */
class Shortcode {
	/**
	 * Initializes the shortcode.
	 */
	public function init(): void {
		add_shortcode( 'plugin-boilerplate', array( $this, 'render_shortcode_plugin_boilerplate' ) );
	}

	/**
	 * Renders the shortcode for the bicycle overview form.
	 *
	 * @param array $atts
	 * @param string $content
	 * @param string $tag
	 *
	 * @return string|bool
	 */
	public function render_shortcode_plugin_boilerplate( $atts = array(), $content = null, string $tag = '' ): string|bool {
		$atts = shortcode_atts( array(), $atts, $tag );

		ob_start();

		wp_enqueue_style( 'js-plugin-boilerplate-frontend-styles' );
		wp_enqueue_script( 'js-plugin-boilerplate-frontend-script' );

		require plugin_dir_path( dirname( __DIR__ ) ) . 'views/bicycle-overview-shortcode.php';

		return ob_get_clean();
	}
}
