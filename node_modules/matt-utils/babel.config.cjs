module.exports = function (api) {

	api.cache( true );

	return {
		presets: [
			[
				'@babel/env',
				{
					targets: {
						node:			'current',
					},
					useBuiltIns: 'usage',
					corejs:			 3,
					modules:		 false,
					debug:			 false,
				}
			]
		],
		// comments: false,
	}
}