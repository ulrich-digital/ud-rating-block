<?php

/**
 * Server-Side-Rendering des Rating-Blocks
 */

defined('ABSPATH') || exit;

/**
 * Rendert die Bewertungs-Kachel im Frontend.
 *
 * @return string HTML-Ausgabe
 */
function ud_rating_render_block(): string {
    global $wpdb;





    // =====================================================
    // 🔹 Optionen laden
    // =====================================================
    $start_date   = get_option('ud_rating_start_date', '');
    $end_date     = get_option('ud_rating_end_date', '');
    $max_displays = intval(get_option('ud_rating_max_displays', 3));
    $dev_mode     = get_option('ud_rating_dev_mode');
    $min_stars_for_google = intval(get_option('ud_rating_min_stars_for_google', 4));

    // =====================================================
    // 🔹 Anzeigezeitraum prüfen
    // =====================================================
    if (!ud_rating_is_within_period($start_date, $end_date)) {
        return '';
    }

    // =====================================================
    // 🔹 Prüfen, ob Nutzer schon bewertet hat
    // =====================================================

    $user_id = $_COOKIE['ud_rating_user_id'] ?? '';
    $already_rated = 0;

    if (!empty($user_id)) {
        $table = $wpdb->prefix . 'ud_rating_reviews';
        $already_rated = (int) $wpdb->get_var(
            $wpdb->prepare("SELECT COUNT(*) FROM $table WHERE ip_address = %s", $user_id)
        );
    }

    // =====================================================
    // 🔹 Sichtbarkeitslogik
    // =====================================================
    if (!$dev_mode) {
        // Block ausblenden, wenn Nutzer schon bewertet hat ODER max. Anzeigen erreicht sind
        if ($already_rated > 0 || $already_rated >= $max_displays) {
            return '';
        }
    }


    // =====================================================
    // 🔹 Texte aus Settings (mit Fallbacks)
    // =====================================================
    $text_question          = get_option('ud_rating_text_question', 'Gefällt dir der neue Auftritt?');
    $text_thanks            = get_option('ud_rating_text_thanks', 'Danke für dein Feedback!');
    $comment_placeholder    = get_option('ud_rating_text_comment_placeholder', 'Möchtest du noch kurz etwas dazu sagen?');
    $after_comment_text     = get_option('ud_rating_text_after_comment', 'Dein Kommentar wurde gespeichert.');
    $text_comment_button    = get_option('ud_rating_text_comment_button', 'Absenden');

    // =====================================================
    // 🔹 Google-Link-Logik mit 50/50-Zufall und Fallback
    // =====================================================
    $google_link_company = trim(get_option('ud_rating_google_link_company', ''));
    $text_google_company = get_option('ud_rating_text_google_company', 'Möchtest du deine Bewertung auf Google teilen?');
    $text_button_company = get_option('ud_rating_text_button_company', 'Jetzt auf Google bewerten');

    // Standard: Kundenlink
    $google_link   = $google_link_company;
    $text_google   = $text_google_company;
    $text_button   = $text_button_company;

    if (empty($google_link_company)) {
        // Kein Kundenlink → immer Ulrich-Digital-Fallback
        $google_link = esc_attr(UD_RATING_FALLBACK_LINK);
        $text_google = esc_attr(UD_RATING_FALLBACK_TEXT);
        $text_button = esc_attr(UD_RATING_FALLBACK_BUTTON);
    } else {
        // Kundenlink vorhanden → 50/50 entscheiden
        if (mt_rand(0, 1) === 1) {
            $google_link = esc_attr(UD_RATING_FALLBACK_LINK);
            $text_google = esc_attr(UD_RATING_FALLBACK_TEXT);
            $text_button = esc_attr(UD_RATING_FALLBACK_BUTTON);
        }
    }

    // =====================================================
    // 🔹 SVG-Stern definieren
    // =====================================================
    $get_star_svg = function ($filled = false) {
        $class = $filled ? 'ud-star is-filled' : 'ud-star';
        return '
		<svg class="' . $class . '" width="20" height="20" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
			<path d="M30.17,33.76 L27.09,24.11 C26.96,23.70 27.11,23.24 27.46,22.99 L35.44,17.31 C35.89,16.99 36.00,16.37 35.68,15.92 C35.49,15.65 35.19,15.50 34.86,15.50 L25.08,15.50 C24.64,15.50 24.26,15.22 24.12,14.81 L20.95,4.87 C20.78,4.35 20.22,4.06 19.69,4.23 C19.38,4.32 19.14,4.57 19.04,4.87 L15.86,14.81 C15.73,15.22 15.35,15.50 14.91,15.50 L5.13,15.50 C4.58,15.50 4.13,15.94 4.13,16.50 C4.13,16.82 4.28,17.12 4.55,17.31 L12.53,22.99 C12.88,23.24 13.04,23.70 12.90,24.11 L9.83,33.76 C9.66,34.29 9.95,34.85 10.47,35.02 C10.79,35.12 11.12,35.06 11.38,34.86 L19.39,28.85 C19.75,28.58 20.24,28.58 20.60,28.85 L28.62,34.86 C29.06,35.20 29.68,35.11 30.02,34.67 C30.21,34.41 30.27,34.07 30.17,33.76 Z" fill="#fabb05"/>
		</svg>';
    };

    // =====================================================
    // 🔹 HTML-Ausgabe
    // =====================================================
    ob_start();

    $delay = intval(get_option('ud_rating_display_delay', 0));
    $inline_style = $delay > 0 ? 'opacity:0;pointer-events:none;transition:opacity 0.6s ease;' : '';

?>
    <div
        <?php echo get_block_wrapper_attributes([
            'class' => 'ud-rating-block' . ($delay > 0 ? ' is-delayed' : ''),
            'style' => $inline_style
        ]); ?>
        data-max-displays="<?php echo esc_attr($max_displays); ?>"
        data-google-link="<?php echo esc_url($google_link); ?>"
        data-min-stars-google="<?php echo esc_attr($min_stars_for_google); ?>"
        data-confetti="<?php echo get_option('ud_rating_enable_confetti') ? '1' : '0'; ?>"
        data-delay="<?php echo esc_attr(get_option('ud_rating_display_delay', 0)); ?>"
        data-dev-mode="<?php echo $dev_mode ? '1' : '0'; ?>"
        data-comment-placeholder="<?php echo esc_attr($comment_placeholder); ?>"
        data-comment-saved="<?php echo esc_attr($after_comment_text); ?>">

        <p class="ud-rating-block__question"><?php echo esc_html($text_question); ?></p>

        <div class="ud-rating-block__stars" data-rated="0">
            <?php for ($i = 1; $i <= 5; $i++) echo $get_star_svg(false); ?>
        </div>

        <div class="ud-rating-block__thankyou" hidden>
            <p><?php echo esc_html($text_thanks); ?></p>
        </div>

        <div class="ud-rating-block__comment" hidden>
            <textarea id="ud-rating-comment" class="ud-rating-block__comment-input" rows="3"
                placeholder="<?php echo esc_attr($comment_placeholder); ?>"></textarea>
            <button type="button" class="ud-rating-block__comment-submit">
                <?php echo esc_html($text_comment_button); ?>
            </button>
        </div>

        <div class="ud-rating-block__google" hidden>
            <p><?php echo esc_html($text_google); ?></p>
            <a href="<?php echo esc_url($google_link); ?>" target="_blank" rel="noopener noreferrer"
                class="ud-rating-block__google-link">
                <?php echo esc_html($text_button); ?>
            </a>
        </div>
    </div>
<?php
    return ob_get_clean();
}
