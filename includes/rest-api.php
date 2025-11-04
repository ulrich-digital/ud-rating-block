<?php

/**
 * REST-API-Endpunkte für den Rating-Block
 * ---------------------------------------
 * /submit  → öffentlich (zum Absenden von Bewertungen)
 * /list    → nur für Administratoren (zur Einsicht der Bewertungen)
 * /stats   → öffentlich (zeigt nur Durchschnitt & Gesamtzahl)
 */

defined('ABSPATH') || exit;

/**
 * =====================================================
 *  🔹 Route: /wp-json/ud-rating/v1/submit
 *  → nimmt Bewertungen entgegen (öffentlich)
 * =====================================================
 */
add_action('rest_api_init', function () {
    register_rest_route('ud-rating/v1', '/submit', [
        'methods'  => 'POST',
        'callback' => 'ud_rating_submit_review',
        'permission_callback' => '__return_true',
    ]);
});

function ud_rating_submit_review(WP_REST_Request $request) {
    global $wpdb;
    $table = $wpdb->prefix . 'ud_rating_reviews';

    // Bewertung & Kommentar aus Request
    $rating  = intval($request->get_param('rating'));
    $comment = sanitize_textarea_field($request->get_param('comment'));
    $user_id = sanitize_text_field($request->get_param('user_id')); // UUID aus LocalStorage

    // Fallback: IP-Adresse als Ersatz
    if (empty($user_id)) {
        $user_id = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }

    // Validierung
    if ($rating < 1 || $rating > 5) {
        return new WP_Error(
            'invalid_rating',
            __('Ungültiger Bewertungswert.', 'rating-block-ud'),
            ['status' => 400]
        );
    }

    // 🔹 Bestehenden Eintrag prüfen (nach UUID)
    $existing_id = $wpdb->get_var(
        $wpdb->prepare("SELECT id FROM $table WHERE ip_address = %s LIMIT 1", $user_id)
    );

    // Prüfen, ob Dev-Mode aktiv ist
    $dev_mode = get_option('ud_rating_dev_mode') ? true : false;

    // 🔹 Wenn Dev-Mode aktiv → immer neuen Eintrag erstellen
    if ($dev_mode) {
        $existing_id = null;
    }
    // 🔹 Daten vorbereiten
    // 🔹 Daten vorbereiten – Reihenfolge exakt wie in der Tabelle

    $data = [
        'rating'     => $rating ?? '',
        'comment'    => $comment ?? '',
        'ip_address' => $user_id ?? '',
        'created_at' => current_time('mysql') ?? '',
    ];
    $formats = ['%d', '%s', '%s', '%s'];






    // 🔹 Insert oder Update
    if ($existing_id) {
        $updated = $wpdb->update($table, $data, ['id' => $existing_id], $formats, ['%d']);
        if ($updated === false) {
            return new WP_Error(
                'db_update_failed',
                __('Bewertung konnte nicht aktualisiert werden.', 'rating-block-ud'),
                ['status' => 500]
            );
        }
        $action = 'updated';
    } else {
        $inserted = $wpdb->insert($table, $data, $formats);
        if ($inserted === false) {
            return new WP_Error(
                'db_insert_failed',
                __('Bewertung konnte nicht gespeichert werden.', 'rating-block-ud'),
                ['status' => 500]
            );
        } else {
            //error_log('[UD-Rating] DB-Insert geglückt');
        }
        $existing_id = $wpdb->insert_id;
        $action = 'inserted';
    }

    // 🔹 Erfolgs-Response
    return [
        'success' => true,
        'action'  => $action,
        'id'      => (int) $existing_id,
        'rating'  => $rating,
        'comment' => $comment ?? '',
    ];
}


/**
 * =====================================================
 *  🔹 Route: /wp-json/ud-rating/v1/list
 *  → gibt Bewertungen zurück (nur Admins)
 * =====================================================
 */
add_action('rest_api_init', function () {
    register_rest_route('ud-rating/v1', '/list', [
        'methods'  => 'GET',
        'callback' => function () {
            global $wpdb;
            $table = $wpdb->prefix . 'ud_rating_reviews';

            $results = $wpdb->get_results("
				SELECT id, rating, comment, created_at
				FROM $table
				ORDER BY id DESC
				LIMIT 100
			");

            return rest_ensure_response($results);
        },
        'permission_callback' => function () {
            return current_user_can('manage_options'); // nur Admins
        },
    ]);
});

/**
 * =====================================================
 *  🔹 Route: /wp-json/ud-rating/v1/stats
 *  → zeigt Durchschnitt & Anzahl (öffentlich)
 * =====================================================
 */
add_action('rest_api_init', function () {
    register_rest_route('ud-rating/v1', '/stats', [
        'methods'  => 'GET',
        'callback' => function () {
            global $wpdb;
            $table = $wpdb->prefix . 'ud_rating_reviews';

            $total = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table");
            $avg   = (float) $wpdb->get_var("SELECT AVG(rating) FROM $table");

            return [
                'total'   => $total,
                'average' => round($avg, 2),
            ];
        },
        'permission_callback' => '__return_true',
    ]);
});
