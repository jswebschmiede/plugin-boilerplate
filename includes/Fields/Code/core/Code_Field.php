<?php

namespace Carbon_Field_Code;

use Carbon_Fields\Field\Field;
use Carbon_Fields\Exception\Incorrect_Syntax_Exception;

class Code_Field extends Field {
	/**
	 * Code editor mode (css, javascript, html only - others will fallback to css).
	 *
	 * @var string
	 */
	protected $mode = 'css';

	/**
	 * Code editor theme (github, kuroir only - others will fallback to kuroir).
	 *
	 * @var string
	 */
	protected $theme = 'kuroir';

	/**
	 * Code editor height.
	 *
	 * @var string
	 */
	protected $height = '300px';

	/**
	 * Code editor width.
	 *
	 * @var string
	 */
	protected $width = '500px';

	/**
	 * Allowed modes for validation.
	 *
	 * @var array
	 */
	protected $allowed_modes = array( 'css', 'javascript', 'html' );

	/**
	 * Allowed themes for validation.
	 *
	 * @var array
	 */
	protected $allowed_themes = array( 'github', 'kuroir', 'monokai' );

	/**
	 * Set the code editor mode.
	 * Allowed modes: css, javascript, html.
	 *
	 * @param string $mode
	 *
	 * @return $this
	 */
	public function set_mode( $mode ) {
		if ( ! in_array( $mode, $this->allowed_modes, true ) ) {
			Incorrect_Syntax_Exception::raise(
				'Invalid Ace Editor mode "' . $mode . '". Allowed modes: ' . implode( ', ', $this->allowed_modes ),
			);
		}

		$this->mode = $mode;
		return $this;
	}

	/**
	 * Set the code editor theme.
	 * Allowed themes: github, kuroir.
	 *
	 * @param string $theme
	 *
	 * @return $this
	 */
	public function set_theme( $theme ) {
		if ( ! in_array( $theme, $this->allowed_themes, true ) ) {
			Incorrect_Syntax_Exception::raise(
				'Invalid Ace Editor theme "' . $theme . '". Allowed themes: ' . implode( ', ', $this->allowed_themes ),
			);
		}

		$this->theme = $theme;
		return $this;
	}

	public function set_height( $height ) {
		$this->height = $height;
		return $this;
	}

	public function set_width( $width ) {
		$this->width = $width;
		return $this;
	}

	/**
	 * Prepare the field type for use.
	 * Called once per field type when activated.
	 *
	 * @static
	 */
	public static function field_type_activated(): void {
		$dir    = \Carbon_Field_Code\DIR . '/languages/';
		$locale = get_locale();
		$path   = $dir . $locale . '.mo';
		load_textdomain( 'carbon-field-code', $path );
	}

	/**
	 * Returns an array that holds the field data, suitable for JSON serialization.
	 * This data will be available in the Underscore template and the Backbone Model.
	 *
	 * @param bool $load should the value be loaded from the database or use the value from the current instance
	 *
	 * @return array
	 */
	public function to_json( $load ) {
		$field_data = parent::to_json( $load );

		$field_data = array_merge(
			$field_data,
			array(
				'mode'   => $this->mode,
				'theme'  => $this->theme,
				'height' => $this->height,
				'width'  => $this->width,
			),
		);

		return $field_data;
	}

	/**
	 * Enqueue scripts and styles in admin.
	 * Called once per field type.
	 *
	 * @static
	 */
	public static function admin_enqueue_scripts(): void {
		$root_uri  = \Carbon_Fields\Carbon_Fields::directory_to_url( \Carbon_Field_Code\DIR );
		$build_dir = \Carbon_Field_Code\DIR . '/build/';

		// Determine file names based on available files (production has .min, development doesn't)
		$css_file = file_exists( "{$build_dir}bundle.min.css" ) ? 'bundle.min.css' : 'bundle.css';
		$js_file  = file_exists( "{$build_dir}bundle.min.js" ) ? 'bundle.min.js' : 'bundle.js';

		// Enqueue field styles.
		wp_enqueue_style( 'carbon-field-code', "{$root_uri}/build/{$css_file}" );

		// Enqueue field scripts.
		wp_enqueue_script( 'carbon-field-code', "{$root_uri}/build/{$js_file}", array( 'carbon-fields-core' ) );
	}
}
