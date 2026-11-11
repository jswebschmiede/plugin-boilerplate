/**
 * Frontend entry. Loaded together with the [plugin_boilerplate] shortcode.
 */
import '../css/frontend.css';

document.addEventListener( 'DOMContentLoaded', () => {
	const sections = document.querySelectorAll( '.plugin-boilerplate' );

	sections.forEach( ( section ) => {
		section.dataset.ready = 'true';
	} );
} );
