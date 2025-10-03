<?php

/**
 * Admin Settings class.
 */

namespace JS\PluginBoilerplate\Backend;

use Carbon_Fields\Field;
use Carbon_Fields\Container;

/**
 * Handles the admin settings page for the plugin
 * Purpose: Provides a settings page in the WordPress admin area for configuring plugin settings.
 */
class AdminSettings {
	/**
	 * @var string The option name for storing settings
	 */
	private const OPTION_NAME = 'js_plugin_boilerplate_settings';

	/**
	 * Initialize the admin settings.
	 */
	public function init(): void {
		add_action( 'carbon_fields_register_fields', array( $this, 'register_carbon_fields' ) );
	}

	/**
	 * Register Carbon Fields for admin settings.
	 */
	public function register_carbon_fields(): void {
		Container::make( 'theme_options', __( 'Plugin Boilerplate Einstellungen', 'plugin-boilerplate' ) )
			// phpcs:ignore Generic.PHP.UnknownMethodName
			->set_page_parent( 'tools.php' )
			->add_tab(
				__( 'Allgemein', 'plugin-boilerplate' ),
				array(
					Field::make( 'color', 'plugin_boilerplate_example_color', __( 'Beispiel Farbe' ) )
						->set_default_value( '#d7e9cc' )
						->set_width( 33 ),

					Field::make( 'separator', 'plugin_boilerplate_custom_css_separator', __( 'Individuelles CSS', 'plugin-boilerplate' ) ),

					Field::make( 'code', 'plugin_boilerplate_custom_code', __( 'CSS', 'plugin-boilerplate' ) )
						->set_help_text( __( 'Füge hier dein eigenes CSS hinzu, um das Erscheinungsbild des Plugin Boilerplates anzupassen.', 'plugin-boilerplate' ) )
						// phpcs:ignore Generic.PHP.UnknownMethodName
						->set_mode( 'css' )
						->set_theme( 'monokai' )
						->set_height( '300px' )
						->set_width( '600px' ),
				),
			)
			->add_tab(
				__( 'Shortcodes', 'plugin-boilerplate' ),
				array(
					Field::make( 'html', 'plugin_boilerplate_shortcodes_info', __( 'Verfügbare Shortcodes', 'plugin-boilerplate' ) )
						// phpcs:ignore Generic.PHP.UnknownMethodName
						->set_html(
							'<p>' . __( 'Die folgenden Shortcodes können auf deiner Website verwendet werden:', 'plugin-boilerplate' ) . '</p>
							<table class="widefat striped">
								<thead>
									<tr>
										<th>' . __( 'Shortcode', 'plugin-boilerplate' ) . '</th>
										<th>' . __( 'Beschreibung', 'plugin-boilerplate' ) . '</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td><code>[plugin-boilerplate]</code></td>
										<td>' . __( 'Zeigt den Plugin Boilerplate an.', 'plugin-boilerplate' ) . '</td>
									</tr>
								</tbody>
							</table>
							<h3 style="margin-top: 15px;">' . __( 'Shortcode-Nutzung', 'plugin-boilerplate' ) . '</h3>
							<ol>
								<li>' . __( 'Kopiere den gewünschten Shortcode aus der obigen Tabelle.', 'plugin-boilerplate' ) . '</li>
								<li>' . __( 'Füge ihn in den Inhalt einer Seite oder eines Beitrags ein.', 'plugin-boilerplate' ) . '</li>
							</ol>',
						),
				),
			);
	}
}
