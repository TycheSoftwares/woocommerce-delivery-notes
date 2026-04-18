// @ts-nocheck
const path = require('path');
const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const BrowserSyncPlugin = require('browser-sync-webpack-plugin');

module.exports = {
	...defaultConfig,
	entry: {
		...defaultConfig.entry,
		admin: path.resolve(__dirname, 'src/admin/index.js'),
	},
	plugins: [
		...defaultConfig.plugins,
		new BrowserSyncPlugin(
			{
				proxy: 'http://localhost:10014/wp-admin/admin.php?page=wcdn_page',
				files: ['**/*.php', 'build/**/*'],
				open: false,
				notify: false,
			},
			{
				reload: true,
			}
		),
	],
	output: {
		...defaultConfig.output,
		filename: '[name].js',
		chunkFilename: '[name].js',
	},
	resolve: {
		...defaultConfig.resolve,
		alias: {
			...defaultConfig.resolve?.alias,
			'@': path.resolve(__dirname, 'src'),
			'@admin': path.resolve(__dirname, 'src/admin'),
		},
	},
};