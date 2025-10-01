<?php

/**
 * The shortcode class.
 *
 * @package plugin-boilerplate
 */

namespace JS\PluginBoilerplate\Shortcodes;

defined('ABSPATH') or die('Thanks for visting');

class Shortcode
{
	/**
	 * Initializes the shortcode.
	 *
	 * @return void
	 */
	public function init(): void
	{
		add_shortcode('plugin-boilerplate', [$this, 'render_shortcode_plugin_boilerplate']);
	}

	/**
	 * Renders the shortcode for the plugin boilerplate form.
	 *
	 * @param array $atts
	 * @param string $content
	 * @param string $tag
	 * @return string|bool
	 */
	public function render_shortcode_plugin_boilerplate($atts = [], $content = null, string $tag = ''): string|bool
	{
		$atts = shortcode_atts([], $atts, $tag);

		ob_start();

		wp_enqueue_style('js-plugin-boilerplate-frontend-styles');
		wp_enqueue_script('js-plugin-boilerplate-frontend-script');

		require plugin_dir_path(dirname(dirname(__FILE__))) . 'views/plugin-boilerplate-shortcode.php';

		return ob_get_clean();
	}
}
