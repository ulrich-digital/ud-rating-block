<?php
/**
 * Plugin Name:     UD Block: Rating Block
 * Description:     Bewertungs-Kachel mit 5-Sterne-System und zeitgesteuerter Anzeige.
 * Version:         1.0.0
 * Author:          ulrich.digital gmbh
 * Author URI:      https://ulrich.digital/
 * License:         GPL v2 or later
 * License URI:     https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:     rating-block-ud
 */

/**
 * Hinweis:
 * Diese Datei dient ausschliesslich als Einstiegspunkt für das Plugin.
 */

defined('ABSPATH') || exit;

foreach ([
    'db.php',               // Datenbank-Tabelle für Bewertungen
    'rest-api.php',         // REST-Endpunkte für Bewertungs-Speicherung
    'helpers.php',          // Allgemeine Hilfsfunktionen (z. B. Google-Link)
    'block-register.php',   // Block-Registrierung via block.json
    'enqueue.php',          // Enqueue von Styles/Scripts
    'render.php',            // PHP-Rendering des Blocks
    'settings.php'          // Optionen-Seite
] as $file) {
    require_once __DIR__ . '/includes/' . $file;
}


register_activation_hook(__FILE__, function () {
    $defaults = [
        'ud_rating_dev_mode'                  => false,
        'ud_rating_start_date'                => '',
        'ud_rating_end_date'                  => '',
        'ud_rating_max_displays'              => 3,
        'ud_rating_display_delay'             => 5,
        'ud_rating_enable_confetti'           => true,
        'ud_rating_text_question'             => 'Gefällt Dir die neue Webseite?',
        'ud_rating_text_thanks'               => 'Vielen Dank für dein Feedback!',
        'ud_rating_text_comment_placeholder'  => 'Deine Meinung interessiert uns.',
        'ud_rating_text_after_comment'        => 'Dein Kommentar wurde gespeichert.',
        'ud_rating_text_comment_button'       => 'Absenden',
        'ud_rating_min_stars_for_google'      => 4,
        'ud_rating_google_link_company'       => '',
        'ud_rating_text_google_company'       => 'Möchtest du deine Bewertung auf Google teilen?',
        'ud_rating_text_button_company'       => 'Bewertung teilen',
        'ud_rating_custom_css'                => '',
    ];

    foreach ($defaults as $key => $value) {
        if (get_option($key) === false) {
            add_option($key, $value);
        }
    }
});

/**
 * 🔹 "Einstellungen"-Link in der Plugin-Übersicht anzeigen
 */
add_filter('plugin_action_links_' . plugin_basename(__FILE__), function ($links) {
    $settings_url = admin_url('options-general.php?page=ud_rating_settings');
    $settings_link = '<a href="' . esc_url($settings_url) . '">' . esc_html__('Einstellungen', 'rating-block-ud') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
});
