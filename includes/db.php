<?php
/**
 * Datenbank-Setup für den Rating-Block
 */

defined('ABSPATH') || exit;

/**
 * Erstellt oder aktualisiert die Tabelle für gespeicherte Bewertungen.
 */
function ud_rating_create_table() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'ud_rating_reviews';
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
		id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		rating TINYINT(1) NOT NULL,
		comment TEXT NULL,
		ip_address VARCHAR(45) DEFAULT NULL,
		created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY  (id)
	) $charset_collate;";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta($sql);
}

/**
 * Beim Aktivieren des Plugins ausführen.
 */
register_activation_hook(__FILE__, 'ud_rating_create_table');

/**
 * Sicherheits-Fallback:
 * Prüft bei jedem Seitenaufruf, ob die Tabelle existiert
 * und ob die Spalte 'comment' vorhanden ist.
 * Fehlt sie, wird sie automatisch ergänzt.
 */
add_action('plugins_loaded', function () {
	global $wpdb;
	$table_name = $wpdb->prefix . 'ud_rating_reviews';

	// Prüfen, ob Tabelle existiert
	$exists = $wpdb->get_var($wpdb->prepare(
		"SHOW TABLES LIKE %s",
		$table_name
	));

	if ($exists !== $table_name) {
		ud_rating_create_table();
		return;
	}

	// Prüfen, ob Spalte 'comment' existiert
	$column_exists = $wpdb->get_results("SHOW COLUMNS FROM $table_name LIKE 'comment'");
	if (empty($column_exists)) {
		$wpdb->query("ALTER TABLE $table_name ADD COLUMN comment TEXT NULL AFTER rating");
	}
});
