<?php
/**
 * Example admin action: toggles the "featured" flag from the list table.
 *
 * @package plugin-boilerplate
 */

declare( strict_types=1 );

namespace Jswebforge\PluginBoilerplate\Controllers;

use Jswebforge\PluginBoilerplate\Common\Hookable;

\defined( 'ABSPATH' ) || exit;

/**
 * Adds a row action and handles the nonce-protected admin request behind it.
 *
 * Pattern: row action link → admin.php?action=… → capability + nonce check →
 * change → redirect back with a notice query arg → notice → arg removed from the URL.
 */
class ToggleController implements Hookable {
	/**
	 * Admin action name (`admin.php?action=…`).
	 */
	public const string ACTION = 'plugin_boilerplate_toggle_featured';

	/**
	 * Query arg that carries the toggled post ID back to the list screen.
	 */
	public const string NOTICE_ARG = 'plugin_boilerplate_toggled';

	/**
	 * Registers hooks.
	 *
	 * @return void
	 */
	public function init(): void {
		add_action( 'admin_action_' . self::ACTION, array( $this, 'handle' ) );
		add_filter( 'post_row_actions', array( $this, 'add_row_action' ), 10, 2 );
		add_action( 'admin_notices', array( $this, 'render_notice' ) );
		add_filter( 'removable_query_args', array( $this, 'add_removable_query_arg' ) );
	}

	/**
	 * Builds the nonce-protected action URL for a post.
	 *
	 * @param int $post_id Post ID.
	 * @return string Raw URL; escape on output.
	 */
	public static function get_action_url( int $post_id ): string {
		return wp_nonce_url(
			add_query_arg(
				array(
					'action' => self::ACTION,
					'post'   => $post_id,
				),
				admin_url( 'admin.php' )
			),
			self::get_nonce_action( $post_id )
		);
	}

	/**
	 * Whether the current user may toggle a post.
	 *
	 * @param \WP_Post $post Post.
	 * @return bool
	 */
	public static function current_user_can_toggle( \WP_Post $post ): bool {
		return PostTypeController::POST_TYPE === $post->post_type
			&& 'trash' !== $post->post_status
			&& current_user_can( 'edit_post', $post->ID );
	}

	/**
	 * Appends the toggle link to the example post type's row actions.
	 *
	 * @param array<string, string> $actions Row action links.
	 * @param \WP_Post              $post    Current post.
	 * @return array<string, string> Row action links.
	 */
	public function add_row_action( array $actions, \WP_Post $post ): array {
		if ( ! self::current_user_can_toggle( $post ) ) {
			return $actions;
		}

		$featured = PostTypeController::is_featured( $post->ID );
		$title    = _draft_or_post_title( $post );

		$actions['plugin_boilerplate_toggle'] = sprintf(
			'<a href="%s" aria-label="%s">%s</a>',
			esc_url( self::get_action_url( $post->ID ) ),
			esc_attr(
				$featured
					/* translators: %s: Post title. */
					? sprintf( __( 'Remove “%s” from featured', 'plugin-boilerplate' ), $title )
					/* translators: %s: Post title. */
					: sprintf( __( 'Mark “%s” as featured', 'plugin-boilerplate' ), $title )
			),
			$featured ? esc_html__( 'Unfeature', 'plugin-boilerplate' ) : esc_html__( 'Feature', 'plugin-boilerplate' )
		);

		return $actions;
	}

	/**
	 * Toggles the flag and redirects back to the list.
	 *
	 * @return void
	 */
	public function handle(): void {
		$post_id = isset( $_GET['post'] ) ? absint( wp_unslash( $_GET['post'] ) ) : 0;

		check_admin_referer( self::get_nonce_action( $post_id ) );

		$post = $post_id > 0 ? get_post( $post_id ) : null;

		if ( ! $post instanceof \WP_Post ) {
			wp_die( esc_html__( 'The requested content was not found.', 'plugin-boilerplate' ), '', array( 'response' => 404 ) );
		}

		if ( ! self::current_user_can_toggle( $post ) ) {
			wp_die( esc_html__( 'You are not allowed to change this content.', 'plugin-boilerplate' ), '', array( 'response' => 403 ) );
		}

		if ( PostTypeController::is_featured( $post->ID ) ) {
			delete_post_meta( $post->ID, PostTypeController::FEATURED_META );
		} else {
			update_post_meta( $post->ID, PostTypeController::FEATURED_META, true );
		}

		wp_safe_redirect(
			add_query_arg(
				array(
					'post_type'      => PostTypeController::POST_TYPE,
					self::NOTICE_ARG => $post->ID,
				),
				admin_url( 'edit.php' )
			)
		);
		exit;
	}

	/**
	 * Shows a success notice on the list screen after toggling.
	 *
	 * @return void
	 */
	public function render_notice(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only display; the ID is validated below.
		$post_id = isset( $_GET[ self::NOTICE_ARG ] ) ? absint( wp_unslash( $_GET[ self::NOTICE_ARG ] ) ) : 0;

		if ( 0 === $post_id || 'edit-' . PostTypeController::POST_TYPE !== get_current_screen()?->id ) {
			return;
		}

		$post = get_post( $post_id );

		if ( ! $post instanceof \WP_Post || ! current_user_can( 'edit_post', $post->ID ) ) {
			return;
		}

		$message = PostTypeController::is_featured( $post->ID )
			/* translators: %s: Post title. */
			? __( '“%s” is now featured.', 'plugin-boilerplate' )
			/* translators: %s: Post title. */
			: __( '“%s” is no longer featured.', 'plugin-boilerplate' );

		printf(
			'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
			esc_html( sprintf( $message, _draft_or_post_title( $post ) ) )
		);
	}

	/**
	 * Removes the notice query arg from the browser URL after the page loads.
	 *
	 * @param string[] $args Removable query args.
	 * @return string[] Removable query args.
	 */
	public function add_removable_query_arg( array $args ): array {
		$args[] = self::NOTICE_ARG;

		return $args;
	}

	/**
	 * Gets the nonce action for a post.
	 *
	 * @param int $post_id Post ID.
	 * @return string Nonce action.
	 */
	private static function get_nonce_action( int $post_id ): string {
		return self::ACTION . '_' . $post_id;
	}
}
