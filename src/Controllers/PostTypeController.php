<?php
/**
 * Example custom post type with taxonomy.
 *
 * @package plugin-boilerplate
 */

declare( strict_types=1 );

namespace Jswebforge\PluginBoilerplate\Controllers;

use Jswebforge\PluginBoilerplate\Common\Hookable;

\defined( 'ABSPATH' ) || exit;

/**
 * Registers the example post type, its taxonomy and meta, and extends its list table.
 */
class PostTypeController implements Hookable {
	/**
	 * Post type name (max. 20 characters).
	 */
	public const string POST_TYPE = 'pb_example';

	/**
	 * Taxonomy name (max. 32 characters).
	 */
	public const string TAXONOMY = 'pb_example_type';

	/**
	 * Meta key of the "featured" flag. The leading underscore hides it from the custom fields box.
	 */
	public const string FEATURED_META = '_plugin_boilerplate_featured';

	/**
	 * List table column of the "featured" flag.
	 */
	private const string FEATURED_COLUMN = 'plugin_boilerplate_featured';

	/**
	 * Registers hooks.
	 *
	 * @return void
	 */
	public function init(): void {
		add_action( 'init', array( $this, 'register' ) );
		add_filter( 'manage_' . self::POST_TYPE . '_posts_columns', array( $this, 'add_columns' ) );
		add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', array( $this, 'render_column' ), 10, 2 );
		add_action( 'restrict_manage_posts', array( $this, 'render_taxonomy_filter' ), 10, 2 );
	}

	/**
	 * Registers the post type, taxonomy and meta.
	 *
	 * @return void
	 */
	public function register(): void {
		register_post_type(
			self::POST_TYPE,
			array(
				'labels'        => array(
					'name'               => _x( 'Examples', 'post type general name', 'plugin-boilerplate' ),
					'singular_name'      => _x( 'Example', 'post type singular name', 'plugin-boilerplate' ),
					'add_new_item'       => __( 'Add New Example', 'plugin-boilerplate' ),
					'edit_item'          => __( 'Edit Example', 'plugin-boilerplate' ),
					'new_item'           => __( 'New Example', 'plugin-boilerplate' ),
					'view_item'          => __( 'View Example', 'plugin-boilerplate' ),
					'search_items'       => __( 'Search Examples', 'plugin-boilerplate' ),
					'not_found'          => __( 'No examples found.', 'plugin-boilerplate' ),
					'not_found_in_trash' => __( 'No examples found in Trash.', 'plugin-boilerplate' ),
					'all_items'          => __( 'All Examples', 'plugin-boilerplate' ),
				),
				'public'        => true,
				'has_archive'   => true,
				'rewrite'       => array( 'slug' => 'examples' ),
				'show_in_rest'  => true,
				'menu_icon'     => 'dashicons-portfolio',
				'menu_position' => 25,
				'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields' ),
			)
		);

		register_taxonomy(
			self::TAXONOMY,
			self::POST_TYPE,
			array(
				'labels'            => array(
					'name'          => _x( 'Example Types', 'taxonomy general name', 'plugin-boilerplate' ),
					'singular_name' => _x( 'Example Type', 'taxonomy singular name', 'plugin-boilerplate' ),
					'all_items'     => __( 'All Example Types', 'plugin-boilerplate' ),
					'edit_item'     => __( 'Edit Example Type', 'plugin-boilerplate' ),
					'add_new_item'  => __( 'Add New Example Type', 'plugin-boilerplate' ),
				),
				'hierarchical'      => true,
				'public'            => true,
				'show_in_rest'      => true,
				'show_admin_column' => true,
				'rewrite'           => array( 'slug' => 'example-type' ),
			)
		);

		register_post_meta(
			self::POST_TYPE,
			self::FEATURED_META,
			array(
				'type'              => 'boolean',
				'single'            => true,
				'default'           => false,
				'show_in_rest'      => true,
				'sanitize_callback' => 'rest_sanitize_boolean',
				'auth_callback'     => static fn( bool $allowed, string $meta_key, int $post_id ): bool => current_user_can( 'edit_post', $post_id ),
			)
		);
	}

	/**
	 * Whether a post is marked as featured.
	 *
	 * @param int $post_id Post ID.
	 * @return bool
	 */
	public static function is_featured( int $post_id ): bool {
		return (bool) get_post_meta( $post_id, self::FEATURED_META, true );
	}

	/**
	 * Adds the "featured" column after the title.
	 *
	 * @param array<string, string> $columns List table columns.
	 * @return array<string, string> List table columns.
	 */
	public function add_columns( array $columns ): array {
		$result = array();

		foreach ( $columns as $key => $label ) {
			$result[ $key ] = $label;

			if ( 'title' === $key ) {
				$result[ self::FEATURED_COLUMN ] = __( 'Featured', 'plugin-boilerplate' );
			}
		}

		return $result;
	}

	/**
	 * Renders the "featured" column.
	 *
	 * @param string $column  Column key.
	 * @param int    $post_id Post ID.
	 * @return void
	 */
	public function render_column( string $column, int $post_id ): void {
		if ( self::FEATURED_COLUMN !== $column ) {
			return;
		}

		if ( self::is_featured( $post_id ) ) {
			printf(
				'<span class="dashicons dashicons-star-filled" aria-hidden="true"></span><span class="screen-reader-text">%s</span>',
				esc_html__( 'Featured', 'plugin-boilerplate' )
			);
			return;
		}

		echo '<span aria-hidden="true">&ndash;</span>';
	}

	/**
	 * Renders a taxonomy dropdown above the list table.
	 *
	 * The select is named after the taxonomy query var, so WordPress filters the
	 * query without a parse_query callback.
	 *
	 * @param string $post_type Current post type.
	 * @param string $which     Position, "top" or "bottom".
	 * @return void
	 */
	public function render_taxonomy_filter( string $post_type, string $which = 'top' ): void {
		if ( self::POST_TYPE !== $post_type || 'top' !== $which ) {
			return;
		}

		$taxonomy = get_taxonomy( self::TAXONOMY );

		if ( ! $taxonomy instanceof \WP_Taxonomy ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only list filter.
		$selected = isset( $_GET[ self::TAXONOMY ] ) ? sanitize_title( wp_unslash( $_GET[ self::TAXONOMY ] ) ) : '';

		printf(
			'<label class="screen-reader-text" for="%s">%s</label>',
			esc_attr( self::TAXONOMY ),
			esc_html( $taxonomy->labels->all_items )
		);

		wp_dropdown_categories(
			array(
				'taxonomy'        => self::TAXONOMY,
				'name'            => self::TAXONOMY,
				'id'              => self::TAXONOMY,
				'value_field'     => 'slug',
				'selected'        => $selected,
				'show_option_all' => $taxonomy->labels->all_items,
				'hide_empty'      => false,
				'hierarchical'    => true,
				'show_count'      => true,
			)
		);
	}
}
