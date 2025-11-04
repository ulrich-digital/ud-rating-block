<?php

/**
 * Enqueue zusätzlicher Scripts und Styles für den Rating-Block (optional).
 */

defined('ABSPATH') || exit;

/**
 * Backend-CSS nur für UD Rating Settings laden
 */
add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook !== 'settings_page_ud_rating_settings') {
        return;
    }

    wp_enqueue_style(
        'ud-rating-settings-style',
        plugins_url('../build/settings-style.css', __FILE__),
        [],
        filemtime(plugin_dir_path(__FILE__) . '../build/settings-style.css')
    );
});

/**
 * 🔹 Frontend: Custom CSS aus den Settings einbinden
 */
/**
 * 🔹 Frontend: Custom CSS aus den Settings einbinden
 */
add_action('wp_enqueue_scripts', function () {
	$custom_css = trim(get_option('ud_rating_custom_css', ''));
	if ($custom_css) {
		// Richtiger Handle gemäss Debug-Ausgabe:
		wp_add_inline_style('ud-rating-block-style', $custom_css);
	}
});