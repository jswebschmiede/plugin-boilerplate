<?php
/**
 * Removes plugin data on uninstall.
 *
 * Runs without the plugin autoloader, so the option name is a literal
 * (SettingsModel::OPTION_NAME). All settings share this one option, so new
 * settings need no change here. On multisite the data is removed from every
 * site in the network. Posts of the example post type are kept.
 *
 * @package plugin-boilerplate
 */

declare( strict_types=1 );

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

$plugin_boilerplate_delete_data = static function (): void {
	delete_option( 'plugin_boilerplate_settings' );
	delete_transient( 'plugin_boilerplate_latest_release' );
};

if ( ! is_multisite() ) {
	$plugin_boilerplate_delete_data();
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
	$plugin_boilerplate_delete_data();
	restore_current_blog();
}
