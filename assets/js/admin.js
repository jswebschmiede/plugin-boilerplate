/**
 * Admin entry. Loaded on the settings page and the example post type screens.
 */
import { __ } from '@wordpress/i18n';
import '../css/admin.css';

document.addEventListener( 'DOMContentLoaded', () => {
	const form = document.querySelector( 'form[action="options.php"]' );

	if ( ! form ) {
		return;
	}

	// Example: warn before leaving the settings page with unsaved changes.
	let dirty = false;

	form.addEventListener( 'change', () => {
		dirty = true;
	} );
	form.addEventListener( 'submit', () => {
		dirty = false;
	} );
	window.addEventListener( 'beforeunload', ( event ) => {
		if ( dirty ) {
			event.preventDefault();
			// Modern browsers show their own text; this string is a fallback.
			event.returnValue = __(
				'You have unsaved changes.',
				'plugin-boilerplate'
			);
		}
	} );
} );
