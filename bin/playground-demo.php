<?php
/**
 * Demo content for the local Playground site (composer playground).
 *
 * Runs as a runPHP step of blueprint.json. Playground keeps the site between
 * starts and runs the blueprint again, so the script exits when the demo page
 * already exists. Not part of the release zip.
 *
 * @package plugin-boilerplate
 */

declare( strict_types=1 );

require_once '/wordpress/wp-load.php';

if ( get_page_by_path( 'plugin-boilerplate-demo' ) instanceof WP_Post ) {
	return;
}

// The plugin may not be loaded yet, so names are literals (see PostTypeController and SettingsModel).
$plugin_boilerplate_post_type = 'pb_example';
$plugin_boilerplate_taxonomy  = 'pb_example_type';

// On the first start the plugin is mounted but not active yet. Activating it runs
// Lifecycle::activate(), which registers the post type, taxonomy and meta.
if ( ! post_type_exists( $plugin_boilerplate_post_type ) ) {
	$plugin_boilerplate_activated = activate_plugin( 'plugin-boilerplate/plugin-boilerplate.php' );

	if ( is_wp_error( $plugin_boilerplate_activated ) ) {
		throw new RuntimeException( esc_html( $plugin_boilerplate_activated->get_error_message() ) );
	}
}

update_option(
	'plugin_boilerplate_settings',
	array(
		'greeting'   => 'Hello from the Playground',
		'intro'      => "Demo content created by bin/playground-demo.php.\nChange the output under Settings → Plugin Boilerplate.",
		'layout'     => 'grid',
		'order'      => 'date',
		'show_date'  => true,
		'post_types' => array( 'post', $plugin_boilerplate_post_type ),
	)
);

$plugin_boilerplate_term = term_exists( 'Demo', $plugin_boilerplate_taxonomy );
if ( ! is_array( $plugin_boilerplate_term ) ) {
	$plugin_boilerplate_term = wp_insert_term( 'Demo', $plugin_boilerplate_taxonomy );
}

foreach ( array( 'First example', 'Second example', 'Third example' ) as $plugin_boilerplate_index => $plugin_boilerplate_title ) {
	$plugin_boilerplate_post_id = wp_insert_post(
		array(
			'post_type'    => $plugin_boilerplate_post_type,
			'post_title'   => $plugin_boilerplate_title,
			'post_content' => 'Example entry created for the Playground demo.',
			'post_status'  => 'publish',
			'post_date'    => gmdate( 'Y-m-d H:i:s', time() - ( $plugin_boilerplate_index + 1 ) * DAY_IN_SECONDS ),
		),
		true
	);

	if ( is_wp_error( $plugin_boilerplate_post_id ) ) {
		throw new RuntimeException( esc_html( $plugin_boilerplate_post_id->get_error_message() ) );
	}

	if ( is_array( $plugin_boilerplate_term ) ) {
		wp_set_object_terms( $plugin_boilerplate_post_id, (int) $plugin_boilerplate_term['term_id'], $plugin_boilerplate_taxonomy );
	}

	if ( 0 === $plugin_boilerplate_index ) {
		update_post_meta( $plugin_boilerplate_post_id, '_plugin_boilerplate_featured', true );
	}
}

wp_insert_post(
	array(
		'post_type'    => 'page',
		'post_name'    => 'plugin-boilerplate-demo',
		'post_title'   => 'Plugin Boilerplate Demo',
		'post_content' => '<!-- wp:shortcode -->[plugin_boilerplate count="10"]<!-- /wp:shortcode -->',
		'post_status'  => 'publish',
	)
);

flush_rewrite_rules();
