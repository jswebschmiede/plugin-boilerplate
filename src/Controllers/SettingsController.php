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
	 * Field types with several inputs, rendered as a fieldset without a row label.
	 */
	private const array GROUPED_TYPES = array( 'radio', 'checkbox', 'checkbox-list' );

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
	 * Registers the option, the section and one settings field per field definition.
	 *
	 * @return void
	 */
	public function register_settings(): void {
		register_setting(
			SettingsModel::OPTION_NAME,
			SettingsModel::OPTION_NAME,
			array(
				'type'              => 'object',
				'sanitize_callback' => array( SettingsModel::class, 'sanitize' ),
			)
		);

		add_settings_section(
			self::SECTION,
			__( 'Shortcode', 'plugin-boilerplate' ),
			array( $this, 'render_section' ),
			SettingsModel::MENU_SLUG
		);

		foreach ( SettingsModel::get_fields() as $key => $field ) {
			$id = SettingsModel::OPTION_NAME . '-' . $key;

			add_settings_field(
				$id,
				$field['label'],
				array( $this, 'render_field' ),
				SettingsModel::MENU_SLUG,
				self::SECTION,
				array(
					'key'       => $key,
					'id'        => $id,
					'field'     => $field,
					// Grouped inputs are labelled by their fieldset legend instead.
					'label_for' => in_array( $field['type'], self::GROUPED_TYPES, true ) ? '' : $id,
				)
			);
		}
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
	 * Renders one settings field with its view from views/fields/.
	 *
	 * @param array{key: string, id: string, field: array<string, mixed>} $args Arguments passed to add_settings_field().
	 * @return void
	 */
	public function render_field( array $args ): void {
		$field = $args['field'];

		View::display(
			'fields/' . $field['type'],
			array(
				'id'          => $args['id'],
				'name'        => SettingsModel::OPTION_NAME . '[' . $args['key'] . ']',
				'value'       => SettingsModel::get( $args['key'] ),
				'label'       => $field['label'],
				'description' => $field['description'] ?? '',
				'choices'     => $field['choices'] ?? array(),
				// Full definition, for type specific keys such as min/max.
				'field'       => $field,
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
}
