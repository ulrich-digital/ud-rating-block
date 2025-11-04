<?php
/**
 * Entfernt beim Deinstallieren optional die Bewertungsdaten.
 *
 * Wird das Plugin im WordPress-Backend gelöscht, erscheint
 * eine Abfrage, ob auch die gespeicherten Bewertungen
 * (Tabelle wp_ud_rating_reviews) entfernt werden sollen.
 */



/**
 * Wenn der Administrator das Plugin im Backend löscht,
 * zeigt WordPress zunächst eine Bestätigungsseite.
 * Dort können wir über eine Checkbox optional festlegen,
 * ob auch die Datenbanktabelle gelöscht werden soll.
 */
// uninstall.php
defined('WP_UNINSTALL_PLUGIN') || exit;
global $wpdb;

if (get_option('ud_rating_delete_on_uninstall')) {
	$table = $wpdb->prefix . 'ud_rating_reviews';
	$wpdb->query("DROP TABLE IF EXISTS {$table}");
}
