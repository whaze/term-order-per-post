const wordpress = require( '@wordpress/eslint-plugin' );

module.exports = [
	...wordpress.configs.recommended,
	{
		rules: {
			'import/no-unresolved': [
				'error',
				{ ignore: [ '^@wordpress/' ] },
			],
		},
	},
];
