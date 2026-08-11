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
    $text_launcher          = get_option('ud_rating_text_launcher', 'Feedback');
    $text_question          = get_option('ud_rating_text_question', 'Gefällt Dir die neue Website?');
    $text_rating_thanks     = get_option('ud_rating_text_rating_thanks', 'Vielen Dank für deine Bewertung.');
    $text_thanks            = get_option('ud_rating_text_thanks', 'Vielen Dank für dein Feedback.');
    $comment_placeholder    = get_option('ud_rating_text_comment_placeholder', 'Deine Meinung interessiert uns.');
    $text_comment_button    = get_option('ud_rating_text_comment_button', 'Feedback senden');

    // =====================================================
    // 🔹 Google-Link mit Zuordnung zu Website-Betreiber oder Agentur
    // =====================================================
    $google_link_company = trim(get_option('ud_rating_google_link_company', ''));
    $text_google_company = get_option('ud_rating_text_google_company', 'Möchtest du deine Bewertung auf Google teilen?');
    $text_button_company = get_option('ud_rating_text_button_company', 'Bewertung auf Google teilen');

    $google_link   = $google_link_company;
    $text_google   = $text_google_company;
    $text_button   = $text_button_company;

    if (empty($google_link_company) || mt_rand(0, 1) === 1) {
        $google_link = UD_RATING_FALLBACK_LINK;
        $text_google = UD_RATING_FALLBACK_TEXT;
        $text_button = UD_RATING_FALLBACK_BUTTON;
    }

    // =====================================================
    // 🔹 SVG-Stern definieren
    // =====================================================
    $get_star_svg = function ($value, $filled = false) {
        $class = $filled ? 'ud-star is-filled' : 'ud-star';
        return '
        <button type="button" class="ud-rating-block__star-button" data-rating="' . esc_attr($value) . '" aria-label="' . esc_attr(sprintf(_n('%d Stern', '%d Sterne', $value, 'rating-block-ud'), $value)) . '" aria-pressed="false">
            <svg class="' . $class . '" viewBox="0 0 69.04 65.89" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg">
                <path d="M66.99,23.6h-23.32L36.48,1.42c-.31-.94-1.13-1.42-1.95-1.42s-1.64.47-1.95,1.42l-7.21,22.18H2.05c-1.99,0-2.81,2.54-1.2,3.71l18.86,13.71-7.21,22.18c-.47,1.44.68,2.69,1.96,2.69.4,0,.82-.12,1.2-.4l18.86-13.71,18.86,13.71c.38.28.8.4,1.2.4,1.28,0,2.42-1.25,1.96-2.69l-7.21-22.18,18.86-13.71c1.61-1.17.78-3.71-1.2-3.71Z"/>
            </svg>
        </button>';
    };

    // =====================================================
    // 🔹 HTML-Ausgabe
    // =====================================================
    ob_start();

    $delay = intval(get_option('ud_rating_display_delay', 6));
    $panel_id = wp_unique_id('ud-rating-panel-');
    $format_thanks = static function ($text) {
        $escaped = esc_html($text);
        return preg_replace('/^(Vielen Dank)\s+/u', '$1<br>', $escaped, 1);
    };
    $formatted_question = preg_replace('/^(Gefällt Dir)\s+/u', '$1<br>', esc_html($text_question), 1);

?>
    <div
        <?php echo get_block_wrapper_attributes([
            'class' => 'ud-rating-block' . ($delay > 0 ? ' is-delayed' : '')
        ]); ?>
        data-max-displays="<?php echo esc_attr($max_displays); ?>"
        data-google-link="<?php echo esc_url($google_link); ?>"
        data-confirmation-animation="<?php echo get_option('ud_rating_enable_confetti') ? '1' : '0'; ?>"
        data-delay="<?php echo esc_attr($delay); ?>"
        data-dev-mode="<?php echo $dev_mode ? '1' : '0'; ?>">

        <div id="<?php echo esc_attr($panel_id); ?>" class="ud-rating-block__panel" role="dialog" aria-modal="true" aria-labelledby="<?php echo esc_attr($panel_id); ?>-title" hidden>
            <button type="button" class="ud-rating-block__close" aria-label="<?php esc_attr_e('Feedback schliessen', 'rating-block-ud'); ?>">
                <svg viewBox="0 0 21 21" aria-hidden="true" focusable="false"><path d="M11.315,10.47,16.47,5.315l-.845-.845L10.47,9.625,5.315,4.47l-.845.845L9.625,10.47,4.47,15.625l.845.845,5.155-5.155,5.155,5.155.845-.845Z"/></svg>
            </button>

            <p id="<?php echo esc_attr($panel_id); ?>-title" class="ud-rating-block__question"><?php echo wp_kses($formatted_question, ['br' => []]); ?></p>
            <p id="<?php echo esc_attr($panel_id); ?>-rating-thanks" class="ud-rating-block__rating-thanks" hidden><?php echo wp_kses($format_thanks($text_rating_thanks), ['br' => []]); ?></p>
            <p id="<?php echo esc_attr($panel_id); ?>-thanks" class="ud-rating-block__thankyou" hidden><?php echo wp_kses($format_thanks($text_thanks), ['br' => []]); ?></p>

            <div class="ud-rating-block__stars" role="group" aria-label="<?php esc_attr_e('Bewertung auswählen', 'rating-block-ud'); ?>" data-rated="0">
                <?php for ($i = 1; $i <= 5; $i++) echo $get_star_svg($i, false); ?>
                <span class="ud-rating-block__sparks" aria-hidden="true">
                    <?php for ($i = 1; $i <= 8; $i++) : ?>
                        <span class="ud-rating-block__spark"></span>
                    <?php endfor; ?>
                </span>
            </div>

            <div class="ud-rating-block__comment" hidden>
                <label class="screen-reader-text" for="<?php echo esc_attr($panel_id); ?>-comment"><?php echo esc_html($comment_placeholder); ?></label>
                <textarea id="<?php echo esc_attr($panel_id); ?>-comment" class="ud-rating-block__comment-input" rows="3" placeholder="<?php echo esc_attr($comment_placeholder); ?>"></textarea>
                <div class="ud-rating-block__actions">
                    <button type="button" class="ud-rating-block__comment-submit"><?php echo esc_html($text_comment_button); ?></button>
                </div>
            </div>

            <?php if (!empty($google_link)) : ?>
                <div class="ud-rating-block__google" hidden>
                    <p><?php echo esc_html($text_google); ?></p>
                    <a href="<?php echo esc_url($google_link); ?>" target="_blank" rel="noopener noreferrer" class="ud-rating-block__google-link"><?php echo esc_html($text_button); ?></a>
                </div>
            <?php endif; ?>

            <p class="ud-rating-block__status" role="status" aria-live="polite"></p>
        </div>

        <button type="button" class="ud-rating-block__launcher" aria-controls="<?php echo esc_attr($panel_id); ?>" aria-expanded="false">
            <svg viewBox="0 0 69.04 65.89" aria-hidden="true" focusable="false"><path d="M66.99,23.6h-23.32L36.48,1.42c-.31-.94-1.13-1.42-1.95-1.42s-1.64.47-1.95,1.42l-7.21,22.18H2.05c-1.99,0-2.81,2.54-1.2,3.71l18.86,13.71-7.21,22.18c-.47,1.44.68,2.69,1.96,2.69.4,0,.82-.12,1.2-.4l18.86-13.71,18.86,13.71c.38.28.8.4,1.2.4,1.28,0,2.42-1.25,1.96-2.69l-7.21-22.18,18.86-13.71c1.61-1.17.78-3.71-1.2-3.71Z"/></svg>
            <span><?php echo esc_html($text_launcher); ?></span>
        </button>
    </div>
<?php
    return ob_get_clean();
}
