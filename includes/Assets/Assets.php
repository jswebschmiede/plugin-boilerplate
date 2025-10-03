<?php

/**
 * plugin assets class.
 */

namespace JS\PluginBoilerplate\Assets;

defined( 'ABSPATH' ) or die( 'Thanks for visting' );

class Assets {
	/**
	 * Initializes the plugin assets.
	 */
	public function init(): void {
		add_action( 'wp_enqueue_scripts', array( $this, 'register_frontend_scripts' ), 999 );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_scripts' ), 999 );
	}

	/**
	 * Registers the admin plugin assets.
	 *
	 * @param string $hook
	 */
	public function register_admin_scripts( string $hook ): void {
		$allowed_hooks = array(
			'toplevel_page_bicycle-overview',
			'post.php',
			'post-new.php',
			'edit.php',
		);

		if ( ! in_array( $hook, $allowed_hooks, true ) ) {
			return;
		}

		if ( in_array( $hook, array( 'post.php', 'post-new.php', 'edit.php' ), true ) ) {
			$post_type = $_GET['post_type'] ?? get_post_type();

			if ( 'js_plugin_boilerplate' !== $post_type ) {
				return;
			}
		}

		$asset_file = plugin_dir_path( dirname( __DIR__, 2 ) ) . 'build/admin-dashboard.asset.php';
		$asset_data = file_exists( $asset_file ) ? include $asset_file : array();

		wp_register_script(
			'js-plugin-boilerplate-dashboard-script',
			plugin_dir_url( dirname( __DIR__, 2 ) ) . 'build/admin-dashboard.js',
			$asset_data['dependencies'],
			$asset_data['version'],
			true,
		);

		$asset_file = plugin_dir_path( dirname( __DIR__, 2 ) ) . 'build/admin-dashboard-style.asset.php';
		$asset_data = file_exists( $asset_file ) ? include $asset_file : array();

		wp_register_style(
			'js-plugin-boilerplate-dashboard-styles',
			plugin_dir_url( dirname( __DIR__, 2 ) ) . 'build/admin-dashboard-style.css',
			$asset_data['dependencies'],
			$asset_data['version'],
			'all',
		);

		wp_enqueue_script( 'js-plugin-boilerplate-dashboard-script' );
		wp_enqueue_style( 'js-plugin-boilerplate-dashboard-styles' );
	}

	/**
	 * Registers the frontend plugin assets.
	 */
	public function register_frontend_scripts(): void {
		$asset_file = plugin_dir_path( dirname( __DIR__, 2 ) ) . 'build/frontend-main.asset.php';
		$asset_data = file_exists( $asset_file ) ? include $asset_file : array();

		wp_register_script(
			'js-plugin-boilerplate-frontend-script',
			plugin_dir_url( dirname( __DIR__, 2 ) ) . 'build/frontend-main.js',
			$asset_data['dependencies'],
			$asset_data['version'],
			true,
		);

		$asset_file = plugin_dir_path( dirname( __DIR__, 2 ) ) . 'build/frontend-style.asset.php';
		$asset_data = file_exists( $asset_file ) ? include $asset_file : array();

		wp_register_style(
			'js-plugin-boilerplate-frontend-styles',
			plugin_dir_url( dirname( __DIR__, 2 ) ) . 'build/frontend-style.css',
			$asset_data['dependencies'],
			$asset_data['version'],
			'all',
		);

		// Auto-enqueue scripts on single bicycle pages for theme functionality
		if ( is_singular() && has_shortcode( get_post()->post_content, 'plugin-boilerplate' ) ) {
			wp_enqueue_script( 'js-plugin-boilerplate-frontend-script' );
			wp_enqueue_style( 'js-plugin-boilerplate-frontend-styles' );

			$this->add_inline_css_variables();
		}
	}

	/**
	 * Add inline CSS variables for the plugin boilerplate shortcode.
	 */
	private function add_inline_css_variables(): void {
		$example_color = carbon_get_theme_option( 'plugin_boilerplate_example_color' ) ? carbon_get_theme_option( 'plugin_boilerplate_example_color' ) : '#000';
		$custom_css    = carbon_get_theme_option( 'plugin_boilerplate_custom_code' ) ? carbon_get_theme_option( 'plugin_boilerplate_custom_code' ) : '';

		$css_variables = '.plugin-boilerplate {
            --bc-tabs-color: ' . esc_attr( $example_color ) . ';
        }';

		wp_add_inline_style( 'js-plugin-boilerplate-frontend-styles', $css_variables );

		// Add custom CSS if provided
		if ( ! empty( $custom_css ) ) {
			wp_add_inline_style( 'js-plugin-boilerplate-frontend-styles', wp_kses( $custom_css, array() ) );
		}
	}
}
