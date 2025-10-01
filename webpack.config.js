const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const path = require('path');

module.exports = {
	...defaultConfig,
	entry: {
		'admin-dashboard': './assets/js/admin/dashboard.js',
		'frontend-main': './assets/js/frontend/frontend.js',
		'admin-dashboard-style': './assets/scss/admin/dashboard.scss',
		'frontend-style': './assets/scss/frontend/frontend.scss'
	},
	output: {
		path: path.resolve(__dirname, 'build'),
		filename: '[name].js'
	}
};
