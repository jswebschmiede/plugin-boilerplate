/**
 * Extends the @wordpress/scripts webpack config with the plugin's entries.
 *
 * Each entry produces build/<name>.js, build/<name>.css (from CSS imported in
 * the entry) and build/<name>.asset.php. Register new entries in
 * src/Common/Assets.php as well.
 */
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const path = require( 'path' );

module.exports = {
	...defaultConfig,
	entry: {
		admin: path.resolve( __dirname, 'assets/js/admin.js' ),
		frontend: path.resolve( __dirname, 'assets/js/frontend.js' ),
	},
	output: {
		...defaultConfig.output,
		path: path.resolve( __dirname, 'build' ),
	},
};
