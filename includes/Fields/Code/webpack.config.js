/**
 * External dependencies.
 */
const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const CssMinimizerPlugin = require('css-minimizer-webpack-plugin');
const TerserPlugin = require('terser-webpack-plugin');
const { ProvidePlugin } = require('webpack');

/**
 * Root path to Carbon Fields
 */
const root = path.resolve(__dirname, '../../../vendor/htmlburger/carbon-fields');

/**
 * Indicates if we're running the build process in production mode.
 *
 * @type {Boolean}
 */
const isProduction = process.env.NODE_ENV === 'production';

module.exports = {
	mode: isProduction ? 'production' : 'development',
	entry: {
		bundle: './src/index.js'
	},
	output: {
		path: path.resolve(__dirname, 'build'),
		filename: isProduction ? '[name].min.js' : '[name].js',
		clean: true
	},
	optimization: {
		minimize: isProduction,
		minimizer: isProduction ? [
			new TerserPlugin({
				parallel: true
			}),
			new CssMinimizerPlugin({
				minimizerOptions: {
					preset: [
						'default',
						{
							discardComments: { removeAll: true }
						}
					]
				}
			})
		] : []
	},
	module: {
		rules: [
			{
				test: /\.js$/,
				exclude: /node_modules/,
				use: {
					loader: 'babel-loader',
					options: {
						cacheDirectory: true,
						cacheCompression: false
					}
				}
			},
			{
				test: /\.scss$/,
				use: [
					MiniCssExtractPlugin.loader,
					{
						loader: 'css-loader',
						options: {
							importLoaders: 2
						}
					},
					{
						loader: 'postcss-loader',
						options: {
							postcssOptions: {
								plugins: [
									'autoprefixer'
								]
							}
						}
					},
					{
						loader: 'sass-loader',
						options: {
							api: 'modern-compiler'
						}
					}
				]
			}
		]
	},
	resolve: {
		alias: {
			'@carbon-fields': root
		}
	},
	externals: [
		'@wordpress/compose',
		'@wordpress/data',
		'@wordpress/element',
		'@wordpress/hooks',
		'@wordpress/i18n',
		'classnames',
		'lodash'
	].reduce((memo, name) => {
		memo[name] = `cf.vendor['${name}']`;

		return memo;
	}, {
		'@carbon-fields/core': 'cf.core'
	}),
	plugins: [
		new MiniCssExtractPlugin({
			filename: isProduction ? '[name].min.css' : '[name].css'
		}),

		new ProvidePlugin({
			'wp.element': '@wordpress/element'
		})
	],
	stats: {
		modules: false,
		hash: false,
		builtAt: false,
		children: false
	}
};
