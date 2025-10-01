module.exports = {
	presets: [
		['@babel/preset-env', {
			modules: false
		}],
		['@babel/preset-react', {
			pragma: 'wp.element.createElement',
			pragmaFrag: 'wp.element.Fragment'
		}]
	],
	plugins: [
		'@babel/plugin-transform-class-properties',
		'@babel/plugin-transform-object-rest-spread',
		'@babel/plugin-syntax-async-generators',
		'@babel/plugin-transform-runtime'
	],
	env: {
		production: {
			plugins: [
				['@wordpress/babel-plugin-makepot', {
					output: 'languages/carbon-field-code.pot'
				}]
			]
		}
	}
};
