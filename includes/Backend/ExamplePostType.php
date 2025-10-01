<?php

declare(strict_types=1);

/**
 * Bicycle Post Type class.
 *
 * @package plugin-boilerplate
 */

namespace JS\PluginBoilerplate\Backend;

use Carbon_Fields\Field;
use Carbon_Fields\Container;

/**
 * Handles the custom post type for bicycles and related taxonomies
 * Purpose: Provides a custom post type for managing bicycles with taxonomies for bicycle types
 */
class ExamplePostType
{
	/**
	 * @var string The post type name
	 */
	private const POST_TYPE = 'js_example';

	/**
	 * @var string The taxonomy name for bicycle types
	 */
	private const TAXONOMY_EXAMPLE_TYPE = 'js_example_type';

	/**
	 * Initialize the bicycle post type and taxonomies
	 *
	 * @return void
	 */
	public function init(): void
	{
		add_action('init', [$this, 'register_post_type']);
		add_action('init', [$this, 'register_taxonomies'], 10);
		add_action('carbon_fields_register_fields', [$this, 'register_carbon_fields'], 20);
		add_action('manage_' . self::POST_TYPE . '_posts_custom_column', [$this, 'populate_admin_columns'], 10, 2);
		add_action('restrict_manage_posts', [$this, 'add_taxonomy_filter']);
		add_action('parse_query', [$this, 'filter_posts_by_taxonomy']);
		add_action('pre_get_posts', [$this, 'handle_example_type_sorting']);
		add_filter('dashboard_recent_posts_query_args', [$this, 'add_example_post_type_to_dashboard']);
		add_filter('manage_edit-' . self::POST_TYPE . '_sortable_columns', [$this, 'add_sortable_columns']);
		add_filter('manage_' . self::POST_TYPE . '_posts_columns', [$this, 'add_admin_columns']);
	}

	/**
	 * Register the example custom post type
	 *
	 * @return void
	 */
	public function register_post_type(): void
	{
		$labels = [
			'name' => _x('Beispiele', 'Post type general name', 'plugin-boilerplate'),
			'singular_name' => _x('Beispiel', 'Post type singular name', 'plugin-boilerplate'),
			'menu_name' => _x('Beispiele', 'Admin Menu text', 'plugin-boilerplate'),
			'name_admin_bar' => _x('Beispiel', 'Add New on Toolbar', 'plugin-boilerplate'),
			'add_new' => __('Neues Beispiel hinzufügen', 'plugin-boilerplate'),
			'add_new_item' => __('Neues Beispiel hinzufügen', 'plugin-boilerplate'),
			'new_item' => __('Neues Beispiel', 'plugin-boilerplate'),
			'edit_item' => __('Beispiel bearbeiten', 'plugin-boilerplate'),
			'view_item' => __('Beispiel ansehen', 'plugin-boilerplate'),
			'all_items' => __('Alle Beispiele', 'plugin-boilerplate'),
			'search_items' => __('Beispiel suchen', 'plugin-boilerplate'),
			'parent_item_colon' => __('Übergeordnetes Beispiel:', 'plugin-boilerplate'),
			'not_found' => __('Keine Beispiele gefunden.', 'plugin-boilerplate'),
			'not_found_in_trash' => __('Keine Beispiele im Papierkorb gefunden.', 'plugin-boilerplate'),
			'featured_image' => _x('Beispiel Bild', 'Overrides the "Featured Image" phrase', 'plugin-boilerplate'),
			'set_featured_image' => _x('Beispiel Bild festlegen', 'Overrides the "Set featured image" phrase', 'plugin-boilerplate'),
			'remove_featured_image' => _x('Beispiel Bild entfernen', 'Overrides the "Remove featured image" phrase', 'plugin-boilerplate'),
			'use_featured_image' => _x('Als Beispiel Bild verwenden', 'Overrides the "Use as featured image" phrase', 'plugin-boilerplate'),
			'archives' => _x('Beispiel Archiv', 'The post type archive label', 'plugin-boilerplate'),
			'insert_into_item' => _x('In Beispiel einfügen', 'Overrides the "Insert into post" phrase', 'plugin-boilerplate'),
			'uploaded_to_this_item' => _x('Zu diesem Beispiel hochgeladen', 'Overrides the "Uploaded to this post" phrase', 'plugin-boilerplate'),
			'filter_items_list' => _x('Beispiel filtern', 'Screen reader text for the filter links', 'plugin-boilerplate'),
			'items_list_navigation' => _x('Beispiel Navigation', 'Screen reader text for the pagination', 'plugin-boilerplate'),
			'items_list' => _x('Beispiel Liste', 'Screen reader text for the items list', 'plugin-boilerplate'),
		];

		$args = [
			'labels' => $labels,
			'public' => false,
			'publicly_queryable' => false,
			'show_ui' => true,
			'show_in_menu' => true,
			'query_var' => true,
			'capability_type' => 'post',
			'has_archive' => true,
			'hierarchical' => false,
			'menu_position' => 20,
			'menu_icon' => 'dashicons-excerpt-view',
			'supports' => ['title', 'editor'],
			'show_in_rest' => false,
			'rewrite' => true,
		];

		register_post_type(self::POST_TYPE, $args);
	}

	/**
	 * Register the example taxonomies
	 *
	 * @return void
	 */
	public function register_taxonomies(): void
	{
		// Bicycle Type Taxonomy
		$labels = [
			'name' => _x('Beispiel Typen', 'Taxonomy General Name', 'plugin-boilerplate'),
			'singular_name' => _x('Beispiel Typen', 'Taxonomy Singular Name', 'plugin-boilerplate'),
			'menu_name' => __('Beispiel Typen', 'plugin-boilerplate'),
			'all_items' => __('Alle Beispiel Typen', 'plugin-boilerplate'),
			'parent_item' => __('Übergeordnete Beispiel Typen', 'plugin-boilerplate'),
			'parent_item_colon' => __('Übergeordnete Beispiel Typen:', 'plugin-boilerplate'),
			'new_item_name' => __('Neue Beispiel Typen', 'plugin-boilerplate'),
			'add_new_item' => __('Neue Beispiel Typen hinzufügen', 'plugin-boilerplate'),
			'edit_item' => __('Beispiel Typen bearbeiten', 'plugin-boilerplate'),
			'update_item' => __('Beispiel Typen aktualisieren', 'plugin-boilerplate'),
			'view_item' => __('Beispiel Typen ansehen', 'plugin-boilerplate'),
			'separate_items_with_commas' => __('Beispiel Typen mit Kommas trennen', 'plugin-boilerplate'),
			'add_or_remove_items' => __('Beispiel Typen hinzufügen oder entfernen', 'plugin-boilerplate'),
			'choose_from_most_used' => __('Aus den häufigsten wählen', 'plugin-boilerplate'),
			'popular_items' => __('Beliebte Beispiel Typen', 'plugin-boilerplate'),
			'search_items' => __('Beispiel Typen suchen', 'plugin-boilerplate'),
			'not_found' => __('Nicht gefunden', 'plugin-boilerplate'),
			'no_terms' => __('Keine Beispiel Typen', 'plugin-boilerplate'),
			'items_list' => __('Beispiel Typen Liste', 'plugin-boilerplate'),
			'items_list_navigation' => __('Beispiel Typen Navigation', 'plugin-boilerplate'),
		];

		$args = [
			'labels' => $labels,
			'hierarchical' => true,
			'public' => true,
			'show_ui' => true,
			'show_admin_column' => false,
			'show_in_nav_menus' => true,
			'show_tagcloud' => false,
			'rewrite' => true,
			'show_in_rest' => true,
		];

		register_taxonomy(self::TAXONOMY_EXAMPLE_TYPE, [self::POST_TYPE], $args);
	}

	/**
	 * Register Carbon Fields for bicycle post type
	 *
	 * @return void
	 *
	 */
	public function register_carbon_fields(): void
	{
		Container::make('post_meta', __('Beispiel Details', 'plugin-boilerplate'))
			->where('post_type', '=', self::POST_TYPE)
			->add_fields(
				[
					Field::make('image', 'js_example_image', __('Beispiel Bild', 'plugin-boilerplate'))
						->set_help_text(__('Wählen Sie ein Bild für das Beispiel aus', 'plugin-boilerplate'))
						->set_required(true)
						// phpcs:ignore Generic.PHP.UnknownMethodName
						->set_type(['image/jpeg', 'image/png'])
						->set_value_type('id'),

					Field::make('textarea', 'js_example_description', __('Beispiel Beschreibung', 'plugin-boilerplate'))
						->set_help_text(__('Geben Sie eine detaillierte Beschreibung des Beispiels ein', 'plugin-boilerplate'))
						->set_required(true)
						// phpcs:ignore Generic.PHP.UnknownMethodName
						->set_rows(6),
				]
			);
	}

	/**
	 * Add custom columns to the admin post list
	 *
	 * @param array $columns Existing columns
	 * @return array Modified columns
	 */
	public function add_admin_columns(array $columns): array
	{
		// Rebuild columns with our custom order
		$reordered_columns = [];

		// Add checkbox if it exists
		if (isset($columns['cb'])) {
			$reordered_columns['cb'] = $columns['cb'];
			unset($columns['cb']);
		}

		// Add our custom columns
		$reordered_columns['js_example_image'] = __('Beispiel Bild', 'plugin-boilerplate');

		// Add remaining columns (date, etc.)
		foreach ($columns as $key => $value) {
			$reordered_columns[$key] = $value;
		}

		return $reordered_columns;
	}

	/**
	 * Populate custom admin columns
	 *
	 * @param string $column Column name
	 * @param int $post_id Post ID
	 * @return void
	 */
	public function populate_admin_columns(string $column, int $post_id): void
	{
		switch ($column) {
			case 'js_example_image':
				$image_id = carbon_get_post_meta($post_id, 'js_example_image');
				if (!empty($image_id)) {
					$image_url = wp_get_attachment_image_url($image_id, 'thumbnail');
					if ($image_url) {
						echo '<img src="' . esc_url($image_url) . '" style="width: 80px; height: 80px;" />';
					} else {
						echo '—';
					}
				} else {
					echo '—';
				}
				break;
		}
	}

	/**
	 * Get the post type name
	 *
	 * @return string
	 */
	public static function get_post_type(): string
	{
		return self::POST_TYPE;
	}

	/**
	 * Get the example type taxonomy name
	 *
	 * @return string
	 */
	public static function get_example_type_taxonomy(): string
	{
		return self::TAXONOMY_EXAMPLE_TYPE;
	}

	/**
	 * Add sortable columns for example fields
	 *
	 * @param array $columns Existing sortable columns
	 * @return array Modified sortable columns
	 */
	public function add_sortable_columns(array $columns): array
	{

		return $columns;
	}

	/**
	 * Add taxonomy filter for example type taxonomy
	 *
	 * @return void
	 */
	public function add_taxonomy_filter(): void
	{
		global $typenow;

		if ($typenow !== self::POST_TYPE) {
			return;
		}

		$selected_example_type = isset($_GET[self::TAXONOMY_EXAMPLE_TYPE]) ? $_GET[self::TAXONOMY_EXAMPLE_TYPE] : '';
		$example_types = get_terms([
			'taxonomy' => self::TAXONOMY_EXAMPLE_TYPE,
			'hide_empty' => false,
		]);

		if (!empty($example_types)) {
			echo '<select name="' . self::TAXONOMY_EXAMPLE_TYPE . '" id="' . self::TAXONOMY_EXAMPLE_TYPE . '">';
			echo '<option value="">' . __('Alle Beispiel Typen', 'plugin-boilerplate') . '</option>';
			foreach ($example_types as $example_type) {
				echo '<option value="' . esc_attr($example_type->slug) . '"' . selected($selected_example_type, $example_type->slug, false) . '>' . esc_html($example_type->name) . '</option>';
			}
			echo '</select>';
		}
	}

	/**
	 * Filter posts by example type taxonomy
	 *
	 * @param \WP_Query $query The query object
	 * @return void
	 */
	public function filter_posts_by_taxonomy(\WP_Query $query): void
	{
		global $typenow;

		if (!is_admin() || !$query->is_main_query() || $typenow !== self::POST_TYPE) {
			return;
		}

		$example_type = isset($_GET[self::TAXONOMY_EXAMPLE_TYPE]) ? $_GET[self::TAXONOMY_EXAMPLE_TYPE] : '';

		if (!empty($example_type)) {
			$tax_query = $query->get('tax_query');
			if (!is_array($tax_query)) {
				$tax_query = [];
			}

			$tax_query[] = [
				'taxonomy' => self::TAXONOMY_EXAMPLE_TYPE,
				'field' => 'slug',
				'terms' => $example_type,
			];

			$query->set('tax_query', $tax_query);
		}
	}

	/**
	 * Add example post type to dashboard
	 *
	 * @param array $args The query arguments
	 * @return array Modified query arguments
	 */
	public function add_example_post_type_to_dashboard(array $args): array
	{
		$args['post_type'] = ['post', self::POST_TYPE];
		return $args;
	}
}
