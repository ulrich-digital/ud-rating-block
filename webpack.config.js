/*
 * Erweiterte Webpack-Konfiguration für WordPress-Plugins.
 *
 * Achtung:
 * Diese Datei wird **nur verwendet**, wenn sie in der package.json explizit referenziert wird!
 *
 * Beispiel (in package.json):
 * "scripts": {
 *   "build": "webpack --config webpack.config.js",
 *   "start": "webpack --watch --config webpack.config.js"
 * }
 *
 * Ohne diese Angabe greift WordPress standardmässig auf die interne Konfiguration von @wordpress/scripts zurück –
 * und ignoriert diese Datei vollständig!
 */

/*
 * Erweiterte Webpack-Konfiguration für WordPress-Plugins.
 */

const defaultConfig = require("@wordpress/scripts/config/webpack.config");
const path = require("path");

module.exports = {
	...defaultConfig,
	entry: {
		// 🔹 JavaScript-Einstiegspunkte
		"editor-script": path.resolve(__dirname, "src/js/editor.js"),
		"frontend-script": path.resolve(__dirname, "src/js/frontend.js"),

		// 🔹 SCSS → CSS
		"editor-style": path.resolve(__dirname, "src/css/editor.scss"),
		"frontend-style": path.resolve(__dirname, "src/css/frontend.scss"),

		// 🔹 Neu: Admin-Styling für die Optionen-Seite
		"settings-style": path.resolve(__dirname, "src/js/settings.js"),
	},
	output: {
		path: path.resolve(__dirname, "build"),
		filename: "[name].js",
	},
};
