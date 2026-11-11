<?php
/**
 * Settings screen built with the Settings API.
 *
 * @package plugin-boilerplate
 */

declare( strict_types=1 );

namespace Jswebforge\PluginBoilerplate\Controllers;

use Jswebforge\PluginBoilerplate\Common\Hookable;
use Jswebforge\PluginBoilerplate\Common\View;
use Jswebforge\PluginBoilerplate\Models\SettingsModel;

\defined( 'ABSPATH' ) || exit;

/**
 * Registers the options page, the settings and their fields.
 */
class SettingsController implements Hookable {
	/**
	 * Settings section id.
	 */
	private const string SECTION = 'plugin_boilerplate_main';

	/**
	 * Registers hooks.
	 *
	 * @return void
	 */
	public function init(): void {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( PLUGIN_BOILERPLATE_PLUGIN_FILE ), array( $this, 'add_settings_link' ) );
	}

	/**
	 * Adds the settings page under Settings.
	 *
	 * @return void
	 */
	public function register_menu(): void {
		add_options_page(
			__( 'Plugin Boilerplate', 'plugin-boilerplate' ),
			__( 'Plugin Boilerplate', 'plugin-boilerplate' ),
			'manage_options',
			SettingsModel::MENU_SLUG,
			array( $this, 'render_page' )
		);
	}

	/**
	 * Registers settings, the section and the fields.
	 *
	 * @return void
	 */
	public function register_settings(): void {
		$this->register_options();

		add_settings_section(
			self::SECTION,
			__( 'Shortcode', 'plugin-boilerplate' ),
			array( $this, 'render_section' ),
			SettingsModel::MENU_SLUG
		);

		$this->add_field(
			SettingsModel::GREETING_OPTION,
			__( 'Greeting', 'plugin-boilerplate' ),
			array( $this, 'render_greeting_field' )
		);
		$this->add_field(
			SettingsModel::LAYOUT_OPTION,
			__( 'Layout', 'plugin-boilerplate' ),
			array( $this, 'render_layout_field' )
		);
		$this->add_field(
			SettingsModel::POST_TYPES_OPTION,
			__( 'Content types', 'plugin-boilerplate' ),
			array( $this, 'render_post_types_field' ),
			false
		);
	}

	/**
	 * Renders the settings page.
	 *
	 * @return void
	 */
	public function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		View::display( 'admin-settings' );
	}

	/**
	 * Renders the section intro.
	 *
	 * @return void
	 */
	public function render_section(): void {
		echo '<p>';
		printf(
			/* translators: %s: Shortcode tag. */
			esc_html__( 'These settings control the output of the %s shortcode.', 'plugin-boilerplate' ),
			'<code>[' . esc_html( ShortcodeController::TAG ) . ']</code>'
		);
		echo '</p>';
	}

	/**
	 * Renders the greeting text field.
	 *
	 * @return void
	 */
	public function render_greeting_field(): void {
		View::display(
			'fields/text',
			array(
				'name'        => SettingsModel::GREETING_OPTION,
				'value'       => SettingsModel::get_greeting(),
				'description' => __( 'Shown above the list. Leave empty to hide it.', 'plugin-boilerplate' ),
			)
		);
	}

	/**
	 * Renders the layout select.
	 *
	 * @return void
	 */
	public function render_layout_field(): void {
		View::display(
			'fields/select',
			array(
				'name'        => SettingsModel::LAYOUT_OPTION,
				'choices'     => SettingsModel::get_layout_choices(),
				'selected'    => SettingsModel::get_layout(),
				'description' => __( 'How the shortcode arranges the entries.', 'plugin-boilerplate' ),
			)
		);
	}

	/**
	 * Renders the post type checkboxes.
	 *
	 * @return void
	 */
	public function render_post_types_field(): void {
		View::display(
			'fields/checkbox-list',
			array(
				'name'        => SettingsModel::POST_TYPES_OPTION,
				'choices'     => SettingsModel::get_post_type_choices(),
				'selected'    => SettingsModel::get_post_types(),
				'description' => __( 'The shortcode lists the latest entries of these content types.', 'plugin-boilerplate' ),
			)
		);
	}

	/**
	 * Adds a "Settings" link to the plugin row on the plugins screen.
	 *
	 * @param array<string, string> $links Action links.
	 * @return array<string, string> Action links.
	 */
	public function add_settings_link( array $links ): array {
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'options-general.php?page=' . SettingsModel::MENU_SLUG ) ),
			esc_html__( 'Settings', 'plugin-boilerplate' )
		);

		return array( 'settings' => $settings_link ) + $links;
	}

	/**
	 * Registers each plugin option with the Settings API.
	 *
	 * @return void
	 */
	private function register_options(): void {
		register_setting(
			SettingsModel::OPTION_GROUP,
			SettingsModel::GREETING_OPTION,
			array(
				'type'              => 'string',
				'sanitize_callback' => array( SettingsModel::class, 'sanitize_greeting' ),
				'default'           => '',
			)
		);
		register_setting(
			SettingsModel::OPTION_GROUP,
			SettingsModel::LAYOUT_OPTION,
			array(
				'type'              => 'string',
				'sanitize_callback' => array( SettingsModel::class, 'sanitize_layout' ),
				'default'           => SettingsModel::LAYOUT_LIST,
			)
		);
		register_setting(
			SettingsModel::OPTION_GROUP,
			SettingsModel::POST_TYPES_OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( SettingsModel::class, 'sanitize_post_types' ),
				'default'           => SettingsModel::DEFAULT_POST_TYPES,
			)
		);
	}

	/**
	 * Adds one settings field to the main section.
	 *
	 * @param string   $id       Option name.
	 * @param string   $title    Field label.
	 * @param callable $callback Field renderer. The callable must output escaped markup.
	 * @param bool     $has_label Whether the title becomes a <label> for the input with id $id.
	 *                            Use false for grouped inputs like checkbox lists.
	 * @return void
	 */
	private function add_field( string $id, string $title, callable $callback, bool $has_label = true ): void {
		add_settings_field(
			$id,
			$title,
			$callback,
			SettingsModel::MENU_SLUG,
			self::SECTION,
			$has_label ? array( 'label_for' => $id ) : array()
		);
	}
}
