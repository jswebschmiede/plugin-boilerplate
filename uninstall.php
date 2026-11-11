<?php
/**
 * Removes plugin data on uninstall.
 *
 * Runs without the plugin autoloader, so option keys are listed as literals.
 * Keep them in sync with SettingsModel. On multisite the options are removed
 * from every site in the network. Posts of the example post type are kept.
 *
 * @package plugin-boilerplate
 */

declare( strict_types=1 );

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

$plugin_boilerplate_delete_options = static function (): void {
	$option_keys = array(
		'plugin_boilerplate_greeting',
		'plugin_boilerplate_layout',
		'plugin_boilerplate_post_types',
	);

	foreach ( $option_keys as $option_key ) {
		delete_option( $option_key );
	}

	delete_transient( 'plugin_boilerplate_latest_release' );
};

if ( ! is_multisite() ) {
	$plugin_boilerplate_delete_options();
	return;
}

$plugin_boilerplate_site_ids = get_sites(
	array(
		'fields' => 'ids',
		'number' => 0,
	)
);

foreach ( $plugin_boilerplate_site_ids as $plugin_boilerplate_site_id ) {
	switch_to_blog( (int) $plugin_boilerplate_site_id );
	$plugin_boilerplate_delete_options();
	restore_current_blog();
}
