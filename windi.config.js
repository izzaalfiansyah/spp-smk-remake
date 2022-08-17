const { defineConfig } = require('windicss/helpers');

module.exports = defineConfig({
	attributify: true,
	plugins: [require('windicss/plugin/forms'), require('windicss/plugin/line-clamp')],
});
