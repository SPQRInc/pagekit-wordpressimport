module.exports = [
	{
		entry: {
			"settings": "./app/views/admin/settings.js"
		},
		output: {
			filename: "./app/bundle/[name].js"
		},
		externals: {
			"lodash": "_",
			"jquery": "jQuery",
			"uikit": "UIkit",
			"vue": "Vue"
		},
		module: {
			loaders: [
				{test: /\.vue$/, loader: "vue"},
				{test: /\.js$/, exclude: /node_modules/, loader: "babel-loader"}
			]
		}
	}

];