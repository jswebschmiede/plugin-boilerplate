<?php
/**
 * Example shortcode.
 *
 * @package plugin-boilerplate
 */

declare( strict_types=1 );

namespace Jswebforge\PluginBoilerplate\Controllers;

use Jswebforge\PluginBoilerplate\Common\Assets;
use Jswebforge\PluginBoilerplate\Common\Hookable;
use Jswebforge\PluginBoilerplate\Common\View;
use Jswebforge\PluginBoilerplate\Models\SettingsModel;

\defined( 'ABSPATH' ) || exit;

/**
 * Renders [plugin_boilerplate count="5"]: the latest entries of the configured post types.
 */
class ShortcodeController implements Hookable {
	/**
	 * Shortcode tag.
	 */
	public const string TAG = 'plugin_boilerplate';

	/**
	 * Default number of entries.
	 */
	private const int DEFAULT_COUNT = 5;

	/**
	 * Upper bound for the count attribute.
	 */
	private const int MAX_COUNT = 20;

	/**
	 * Registers the shortcode.
	 *
	 * @return void
	 */
	public function init(): void {
		add_shortcode( self::TAG, array( $this, 'render' ) );
	}

	/**
	 * Renders the shortcode.
	 *
	 * @param array<string, string>|string $atts Shortcode attributes; an empty string without attributes.
	 * @return string Markup.
	 */
	public function render( array|string $atts = array() ): string {
		$atts = shortcode_atts(
			array( 'count' => (string) self::DEFAULT_COUNT ),
			is_array( $atts ) ? $atts : array(),
			self::TAG
		);

		$count      = max( 1, min( self::MAX_COUNT, absint( $atts['count'] ) ) );
		$post_types = (array) SettingsModel::get( 'post_types' );

		// Assets are enqueued here instead of on every page; WordPress prints late styles in the footer.
		Assets::enqueue_frontend();

		return View::render(
			'shortcode',
			array(
				'greeting' => (string) SettingsModel::get( 'greeting' ),
				'intro'    => (string) SettingsModel::get( 'intro' ),
				'layout'   => (string) SettingsModel::get( 'layout' ),
				'items'    => array() === $post_types ? array() : $this->get_items( $post_types, $count ),
			)
		);
	}

	/**
	 * Loads the latest published entries.
	 *
	 * @param string[] $post_types Post type names.
	 * @param int      $count      Number of entries.
	 * @return array<int, array{title: string, url: string, date: string, featured: bool}> Date is empty when hidden.
	 */
	private function get_items( array $post_types, int $count ): array {
		$by_title  = 'title' === SettingsModel::get( 'order' );
		$show_date = (bool) SettingsModel::get( 'show_date' );

		$query = new \WP_Query(
			array(
				'post_type'           => $post_types,
				'post_status'         => 'publish',
				'posts_per_page'      => $count,
				'orderby'             => $by_title ? 'title' : 'date',
				'order'               => $by_title ? 'ASC' : 'DESC',
				'no_found_rows'       => true,
				'ignore_sticky_posts' => true,
			)
		);

		$items = array();

		foreach ( $query->posts as $post ) {
			if ( ! $post instanceof \WP_Post ) {
				continue;
			}

			$items[] = array(
				'title'    => get_the_title( $post ),
				'url'      => (string) get_permalink( $post ),
				'date'     => $show_date ? (string) get_the_date( '', $post ) : '',
				'featured' => PostTypeController::is_featured( $post->ID ),
			);
		}

		return $items;
	}
}
