<?php
/**
 * Plugin updates from GitHub releases.
 *
 * @package plugin-boilerplate
 */

declare( strict_types=1 );

namespace Jswebforge\PluginBoilerplate\Common;

\defined( 'ABSPATH' ) || exit;

/**
 * Offers new versions from the latest GitHub release through the WordPress updater.
 *
 * Requires the "Update URI" header in the main plugin file to point at github.com,
 * which makes WordPress skip wordpress.org and fire update_plugins_github.com.
 */
class Updater implements Hookable {
	/**
	 * GitHub repository in owner/name form.
	 */
	private const string REPOSITORY = 'jswebschmiede/plugin-boilerplate';

	/**
	 * Transient that caches the latest release data.
	 */
	private const string CACHE_KEY = 'plugin_boilerplate_latest_release';

	/**
	 * Cache lifetime after a successful API response.
	 */
	private const int CACHE_TTL = 6 * HOUR_IN_SECONDS;

	/**
	 * Cache lifetime after a failed API response, to stay below the API rate limit.
	 */
	private const int ERROR_CACHE_TTL = HOUR_IN_SECONDS;

	/**
	 * Plugin basename, e.g. plugin-boilerplate/plugin-boilerplate.php.
	 */
	private readonly string $basename;

	/**
	 * Plugin slug (directory name).
	 */
	private readonly string $slug;

	/**
	 * Sets up plugin identifiers.
	 */
	public function __construct() {
		$this->basename = plugin_basename( PLUGIN_BOILERPLATE_PLUGIN_FILE );
		$this->slug     = dirname( $this->basename );
	}

	/**
	 * Registers update hooks. Runs outside of is_admin() because update checks also run via cron.
	 *
	 * @return void
	 */
	public function init(): void {
		add_filter( 'update_plugins_github.com', array( $this, 'check_update' ), 10, 3 );
		add_filter( 'plugins_api', array( $this, 'plugin_information' ), 10, 3 );
		add_action( 'upgrader_process_complete', array( $this, 'clear_cache' ), 10, 2 );
	}

	/**
	 * Supplies update data for this plugin to wp_update_plugins().
	 *
	 * @param array<string, mixed>|false $update      Update data from earlier callbacks, or false.
	 * @param array<string, string>      $plugin_data Plugin header data.
	 * @param string                     $plugin_file Plugin basename.
	 * @return array<string, mixed>|false Update data, or the unchanged input for other plugins.
	 */
	public function check_update( $update, array $plugin_data, string $plugin_file ): array|false {
		if ( $this->basename !== $plugin_file ) {
			return $update;
		}

		$release = $this->get_latest_release();
		if ( null === $release ) {
			return $update;
		}

		return array(
			'slug'    => $this->slug,
			'version' => $release['version'],
			'url'     => $release['url'],
			'package' => $release['package'],
		);
	}

	/**
	 * Provides data for the "View details" modal on the plugins screen.
	 *
	 * @param false|object|array<string, mixed> $result Result from earlier callbacks.
	 * @param string                            $action API action.
	 * @param object                            $args   Request arguments.
	 * @return false|object|array<string, mixed> Plugin information, or the unchanged input.
	 */
	public function plugin_information( $result, string $action, object $args ): mixed {
		if ( 'plugin_information' !== $action || ! isset( $args->slug ) || $this->slug !== $args->slug ) {
			return $result;
		}

		$release = $this->get_latest_release();
		if ( null === $release ) {
			return $result;
		}

		$plugin_data = get_plugin_data( PLUGIN_BOILERPLATE_PLUGIN_FILE, false, false );

		return (object) array(
			'name'          => $plugin_data['Name'],
			'slug'          => $this->slug,
			'version'       => $release['version'],
			'author'        => $plugin_data['Author'],
			'homepage'      => $release['url'],
			'requires'      => $plugin_data['RequiresWP'],
			'requires_php'  => $plugin_data['RequiresPHP'],
			'last_updated'  => $release['published'],
			'download_link' => $release['package'],
			'sections'      => array(
				'description' => esc_html( $plugin_data['Description'] ),
				'changelog'   => wpautop( esc_html( $release['notes'] ) ),
			),
		);
	}

	/**
	 * Drops the cached release after this plugin was updated.
	 *
	 * @param \WP_Upgrader         $upgrader Upgrader instance.
	 * @param array<string, mixed> $options  Upgrade details.
	 * @return void
	 */
	public function clear_cache( \WP_Upgrader $upgrader, array $options ): void {
		if ( 'update' !== ( $options['action'] ?? '' ) || 'plugin' !== ( $options['type'] ?? '' ) ) {
			return;
		}

		$plugins = (array) ( $options['plugins'] ?? array() );
		if ( in_array( $this->basename, $plugins, true ) ) {
			delete_transient( self::CACHE_KEY );
		}
	}

	/**
	 * Returns the latest published release that carries a plugin zip.
	 *
	 * @return array{version: string, url: string, package: string, notes: string, published: string}|null Release data, or null if unavailable.
	 */
	private function get_latest_release(): ?array {
		$cached = get_transient( self::CACHE_KEY );
		if ( is_array( $cached ) ) {
			return array() === $cached ? null : $cached;
		}

		$release = $this->fetch_latest_release();

		// An empty array marks a failed lookup so errors are cached too.
		set_transient( self::CACHE_KEY, $release ?? array(), null === $release ? self::ERROR_CACHE_TTL : self::CACHE_TTL );

		return $release;
	}

	/**
	 * Requests the latest release from the GitHub API.
	 *
	 * @return array{version: string, url: string, package: string, notes: string, published: string}|null Release data, or null on failure.
	 */
	private function fetch_latest_release(): ?array {
		$response = wp_remote_get(
			'https://api.github.com/repos/' . self::REPOSITORY . '/releases/latest',
			array(
				'timeout' => 10,
				'headers' => array(
					'Accept'     => 'application/vnd.github+json',
					'User-Agent' => 'plugin-boilerplate/' . PLUGIN_BOILERPLATE_PLUGIN_VERSION,
				),
			)
		);

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return null;
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! is_array( $data ) || ! is_string( $data['tag_name'] ?? null ) ) {
			return null;
		}

		$version = ltrim( $data['tag_name'], 'vV' );
		if ( 1 !== preg_match( '/^\d+(\.\d+)*([.-][0-9A-Za-z.-]+)?$/', $version ) ) {
			return null;
		}

		$package = $this->find_package_url( (array) ( $data['assets'] ?? array() ), $version );
		if ( null === $package ) {
			return null;
		}

		return array(
			'version'   => $version,
			'url'       => esc_url_raw( (string) ( $data['html_url'] ?? 'https://github.com/' . self::REPOSITORY ) ),
			'package'   => $package,
			'notes'     => (string) ( $data['body'] ?? '' ),
			'published' => (string) ( $data['published_at'] ?? '' ),
		);
	}

	/**
	 * Picks the uploaded plugin zip from the release assets.
	 *
	 * Prefers <slug>-<version>.zip as built by bin/package.php, falls back to the first zip.
	 * GitHub's generated source archives are not assets and are never used.
	 *
	 * @param array<int, mixed> $assets  Release assets from the API.
	 * @param string            $version Release version.
	 * @return string|null Download URL, or null if no zip was attached.
	 */
	private function find_package_url( array $assets, string $version ): ?string {
		$expected = $this->slug . '-' . $version . '.zip';
		$fallback = null;

		foreach ( $assets as $asset ) {
			if ( ! is_array( $asset ) || ! is_string( $asset['name'] ?? null ) || ! is_string( $asset['browser_download_url'] ?? null ) ) {
				continue;
			}

			if ( ! str_starts_with( $asset['browser_download_url'], 'https://github.com/' ) ) {
				continue;
			}

			if ( $expected === $asset['name'] ) {
				return esc_url_raw( $asset['browser_download_url'] );
			}

			if ( null === $fallback && str_ends_with( $asset['name'], '.zip' ) ) {
				$fallback = esc_url_raw( $asset['browser_download_url'] );
			}
		}

		return $fallback;
	}
}
