<?php

/**
 * Hilfsfunktionen für den Rating-Block
 */

defined('ABSPATH') || exit;

// ===============================================================
// 🌟 ulrich.digital – Angaben für die Agentur-Zuordnung
// ===============================================================
define('UD_RATING_FALLBACK_LINK', 'https://g.page/r/CSweGOK6JgY0EB0/review');
define('UD_RATING_FALLBACK_TEXT', 'Möchtest du die Macher dahinter bewerten?');
define('UD_RATING_FALLBACK_BUTTON', 'Agentur bewerten');

/**
 * Prüft, ob der aktuelle Zeitpunkt innerhalb des definierten Anzeigezeitraums liegt.
 *
 * @param string $start Startdatum (ISO-8601 oder leer)
 * @param string $end   Enddatum (ISO-8601 oder leer)
 * @return bool
 */
function ud_rating_is_within_period(?string $start, ?string $end): bool {
    $now = current_time('timestamp');

    if (!empty($start)) {
        $start_ts = strtotime($start);
        if ($start_ts && $now < $start_ts) {
            return false;
        }
    }

    if (!empty($end)) {
        $end_ts = strtotime($end);
        if ($end_ts && $now > $end_ts) {
            return false;
        }
    }

    return true;
}
