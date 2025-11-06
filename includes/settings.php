<?php

/**
 * Admin-Einstellungsseite für den Rating-Block
 */

defined('ABSPATH') || exit;

/**
 * Menüpunkt unter "Einstellungen" hinzufügen.
 */
add_action('admin_menu', function () {
    add_options_page(
        __('UD Rating Block', 'rating-block-ud'),
        __('UD Rating Block', 'rating-block-ud'),
        'manage_options',
        'ud_rating_settings',
        'ud_rating_render_settings_page'
    );
});

/**
 * Einstellungen registrieren.
 */
add_action('admin_init', function () {

    /* =============================================================== *\
	   ⏱️ Anzeigezeitraum & Häufigkeit
	\* =============================================================== */
    add_settings_section(
        'ud_rating_section_schedule',
        __('Anzeigezeitraum & Häufigkeit', 'rating-block-ud'),
        '__return_false',
        'ud_rating_settings'
    );

    // Startdatum
    register_setting('ud_rating_settings_group', 'ud_rating_start_date', [
        'type' => 'string',
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    add_settings_field(
        'ud_rating_start_date',
        __('Startdatum der Anzeige', 'rating-block-ud'),
        fn() => print('<input type="datetime-local" name="ud_rating_start_date" value="' . esc_attr(get_option('ud_rating_start_date', '')) . '">'),
        'ud_rating_settings',
        'ud_rating_section_schedule'
    );

    // Enddatum
    register_setting('ud_rating_settings_group', 'ud_rating_end_date', [
        'type' => 'string',
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    add_settings_field(
        'ud_rating_end_date',
        __('Enddatum der Anzeige', 'rating-block-ud'),
        fn() => print('<input type="datetime-local" name="ud_rating_end_date" value="' . esc_attr(get_option('ud_rating_end_date', '')) . '">'),
        'ud_rating_settings',
        'ud_rating_section_schedule'
    );

    // Maximale Anzeigen pro Nutzer
    register_setting('ud_rating_settings_group', 'ud_rating_max_displays', [
        'type' => 'integer',
        'default' => 3,
        'sanitize_callback' => 'absint',
    ]);
    add_settings_field(
        'ud_rating_max_displays',
        __('Maximale Anzeigen pro Nutzer', 'rating-block-ud'),
        fn() => print('<input type="number" min="1" max="10" name="ud_rating_max_displays" value="' . esc_attr(get_option('ud_rating_max_displays', 3)) . '">'),
        'ud_rating_settings',
        'ud_rating_section_schedule'
    );

    // Verzögerung
    register_setting('ud_rating_settings_group', 'ud_rating_display_delay', [
        'type' => 'integer',
        'default' => 5,
        'sanitize_callback' => 'absint',
    ]);
    add_settings_field(
        'ud_rating_display_delay',
        __('Verzögerung der Einblendung', 'rating-block-ud'),
        fn() => print('<input type="number" name="ud_rating_display_delay" min="0" step="1" value="' . esc_attr(get_option('ud_rating_display_delay', 5)) . '"> <p class="description">' . esc_html__('Zeit in Sekunden, bis der Block angezeigt wird (0 = sofort).', 'rating-block-ud') . '</p>'),
        'ud_rating_settings',
        'ud_rating_section_schedule'
    );


    /* =============================================================== *\
	   💬 Texte & Benutzerführung
	\* =============================================================== */
    add_settings_section(
        'ud_rating_section_texts',
        __('Texte & Benutzerführung', 'rating-block-ud'),
        '__return_false',
        'ud_rating_settings'
    );

    $text_fields = [
        'ud_rating_text_question' => ['Frage an Nutzer', 'Gefällt dir die neue Webseite?'],
        'ud_rating_text_thanks' => ['Dankestext nach Bewertung', 'Vielen Dank für dein Feedback!'],
        'ud_rating_text_comment_placeholder' => ['Platzhalter für Kommentarfeld', 'Deine Meinung interessiert uns.'],
        'ud_rating_text_after_comment' => ['Text nach Kommentar', 'Dein Kommentar wurde gespeichert.'],
        'ud_rating_text_comment_button' => ['Button-Text für Kommentar absenden', 'Absenden'],
    ];

    foreach ($text_fields as $option => [$label, $default]) {
        register_setting('ud_rating_settings_group', $option, [
            'type' => 'string',
            'default' => $default,
            'sanitize_callback' => 'sanitize_text_field',
        ]);
        add_settings_field(
            $option,
            esc_html__($label, 'rating-block-ud'),
            fn() => print('<input type="text" name="' . esc_attr($option) . '" value="' . esc_attr(get_option($option, $default)) . '" class="regular-text">'),
            'ud_rating_settings',
            'ud_rating_section_texts'
        );
    }


    /* =============================================================== *\
	   ⭐ Google-Verknüpfung & Attribution
	\* =============================================================== */
    add_settings_section(
        'ud_rating_section_google',
        __('Google-Verknüpfung & Attribution', 'rating-block-ud'),
        '__return_false',
        'ud_rating_settings'
    );

    register_setting('ud_rating_settings_group', 'ud_rating_min_stars_for_google', [
        'type' => 'integer',
        'default' => 4,
        'sanitize_callback' => fn($v) => max(1, min(5, (int)$v)),
    ]);
    add_settings_field(
        'ud_rating_min_stars_for_google',
        __('Mindestbewertung für Google-Link', 'rating-block-ud'),
        fn() => print('<input type="range" name="ud_rating_min_stars_for_google" min="1" max="5" step="1" value="' . esc_attr(get_option('ud_rating_min_stars_for_google', 4)) . '" oninput="this.nextElementSibling.value=this.value"><output style="font-weight:600;">' . esc_html(get_option('ud_rating_min_stars_for_google', 4)) . '</output>'),
        'ud_rating_settings',
        'ud_rating_section_google'
    );

    register_setting('ud_rating_settings_group', 'ud_rating_google_link_company', [
        'type' => 'string',
        'default' => '',
        'sanitize_callback' => 'esc_url_raw',
    ]);
    add_settings_field(
        'ud_rating_google_link_company',
        __('Google-Link (Kundenprofil)', 'rating-block-ud'),
        fn() => print('<input type="url" name="ud_rating_google_link_company" value="' . esc_attr(get_option('ud_rating_google_link_company', '')) . '" class="regular-text" placeholder="https://g.page/.../review">'),
        'ud_rating_settings',
        'ud_rating_section_google'
    );

    register_setting('ud_rating_settings_group', 'ud_rating_text_google_company', [
        'type' => 'string',
        'default' => 'Möchtest du deine Bewertung auf Google teilen?',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    add_settings_field(
        'ud_rating_text_google_company',
        __('Frage zur Google-Bewertung', 'rating-block-ud'),
        fn() => print('<input type="text" name="ud_rating_text_google_company" value="' . esc_attr(get_option('ud_rating_text_google_company', '')) . '" class="regular-text">'),
        'ud_rating_settings',
        'ud_rating_section_google'
    );

    register_setting('ud_rating_settings_group', 'ud_rating_text_button_company', [
        'type' => 'string',
        'default' => 'Bewertung teilen',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    add_settings_field(
        'ud_rating_text_button_company',
        __('Button-Text', 'rating-block-ud'),
        fn() => print('<input type="text" name="ud_rating_text_button_company" value="' . esc_attr(get_option('ud_rating_text_button_company', '')) . '" class="regular-text">'),
        'ud_rating_settings',
        'ud_rating_section_google'
    );

    add_settings_field(
        'ud_rating_fallback_info',
        __('Einträge von ulrich.digital', 'rating-block-ud'),
        function () {
            echo '<input type="text" readonly class="regular-text" value="' . esc_attr(UD_RATING_FALLBACK_LINK) . '" style="opacity:.6;">';
            echo '<p class="description">' . esc_html__('Fester Agentur-Link', 'rating-block-ud') . '</p>';
            echo '<input type="text" readonly class="regular-text" value="' . esc_attr(UD_RATING_FALLBACK_TEXT) . '" style="opacity:.6;">';
            echo '<p class="description">' . esc_html__('Frage-Text für ulrich.digital', 'rating-block-ud') . '</p>';
            echo '<input type="text" readonly class="regular-text" value="' . esc_attr(UD_RATING_FALLBACK_BUTTON) . '" style="opacity:.6;">';
            echo '<p class="description">' . esc_html__('Button-Text für ulrich.digital', 'rating-block-ud') . '</p>';
        },
        'ud_rating_settings',
        'ud_rating_section_google'
    );


    /* =============================================================== *\
	   🎨 Darstellung & Verhalten
	\* =============================================================== */
    add_settings_section(
        'ud_rating_section_display',
        __('Darstellung & Verhalten', 'rating-block-ud'),
        '__return_false',
        'ud_rating_settings'
    );

    register_setting('ud_rating_settings_group', 'ud_rating_enable_confetti', [
        'type' => 'boolean',
        'default' => false,
        'sanitize_callback' => fn($v) => (bool)$v,
    ]);
    add_settings_field(
        'ud_rating_enable_confetti',
        __('Confetti-Splash aktivieren', 'rating-block-ud'),
        fn() => print('<label><input type="checkbox" name="ud_rating_enable_confetti" value="1" ' . checked(get_option('ud_rating_enable_confetti', false), true, false) . '> ' . esc_html__('Zeigt beim erfolgreichen Absenden eine kurze Konfetti-Animation.', 'rating-block-ud') . '</label>'),
        'ud_rating_settings',
        'ud_rating_section_display'
    );

    register_setting('ud_rating_settings_group', 'ud_rating_custom_css', [
        'type' => 'string',
        'default' => '',
        'sanitize_callback' => 'wp_strip_all_tags',
    ]);
    add_settings_field(
        'ud_rating_custom_css',
        __('Eigenes CSS', 'rating-block-ud'),
        fn() => print('<textarea name="ud_rating_custom_css" rows="6" class="large-text code">' . esc_textarea(get_option('ud_rating_custom_css', '')) . '</textarea><p class="description">' . esc_html__('Eigene CSS-Anpassungen für das Frontend.', 'rating-block-ud') . '</p>'),
        'ud_rating_settings',
        'ud_rating_section_display'
    );


    /* =============================================================== *\
	   ⚙️ Verwaltung & System
	\* =============================================================== */
    add_settings_section(
        'ud_rating_section_admin',
        __('Verwaltung & System', 'rating-block-ud'),
        '__return_false',
        'ud_rating_settings'
    );

    register_setting('ud_rating_settings_group', 'ud_rating_delete_on_uninstall', [
        'type' => 'boolean',
        'default' => false,
    ]);
    add_settings_field(
        'ud_rating_delete_on_uninstall',
        __('Bewertungsdaten beim Löschen entfernen', 'rating-block-ud'),
        fn() => print('<label><input type="checkbox" name="ud_rating_delete_on_uninstall" value="1" ' . checked(get_option('ud_rating_delete_on_uninstall', 0), 1, false) . '> ' . esc_html__('Beim Entfernen des Plugins auch alle gespeicherten Bewertungen löschen', 'rating-block-ud') . '</label>'),
        'ud_rating_settings',
        'ud_rating_section_admin'
    );

    register_setting('ud_rating_settings_group', 'ud_rating_dev_mode', [
        'type' => 'boolean',
        'default' => false,
        'sanitize_callback' => fn($v) => (bool)$v,
    ]);
    add_settings_field(
        'ud_rating_dev_mode',
        __('Entwicklermodus aktivieren', 'rating-block-ud'),
        fn() => print('<label><input type="checkbox" name="ud_rating_dev_mode" value="1" ' . checked(get_option('ud_rating_dev_mode', false), true, false) . '> ' . esc_html__('Erlaubt unbegrenzte Bewertungen zu Testzwecken', 'rating-block-ud') . '</label>'),
        'ud_rating_settings',
        'ud_rating_section_admin'
    );
});


/* =============================================================== *\
   🧩 Einstellungsseite & Bewertungen
\* =============================================================== */

function ud_rating_render_settings_page() {
?>
    <div class="wrap ud-rating-settings-wrap">
        <h1><?php esc_html_e('UD Rating Block', 'rating-block-ud'); ?></h1>

        <nav class="ud-rating-tabs">
            <button class="ud-tab-button is-active" data-tab="tab-settings"><?php esc_html_e('Einstellungen', 'rating-block-ud'); ?></button>
            <button class="ud-tab-button" data-tab="tab-reviews"><?php esc_html_e('Bewertungen', 'rating-block-ud'); ?></button>
        </nav>

        <!-- Einstellungen -->
        <section id="tab-settings" class="ud-tab-content is-active">
            <form method="post" action="options.php">
                <?php
                settings_fields('ud_rating_settings_group');
                do_settings_sections('ud_rating_settings');
                submit_button(__('Änderungen speichern', 'rating-block-ud'));
                ?>
            </form>
        </section>

        <!-- Bewertungen -->
        <section id="tab-reviews" class="ud-tab-content">

            <h2><?php esc_html_e('Bewertungsübersicht', 'rating-block-ud'); ?></h2>




            <?php
            /* =============================================================== *\
                Statistik
            \* =============================================================== */
            ?>
            <?php
            global $wpdb;
            $table = $wpdb->prefix . 'ud_rating_reviews';

            $total_reviews  = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table}");
            $average_rating = (float) $wpdb->get_var("SELECT AVG(rating) FROM {$table}");
            $average_rating = round($average_rating, 1);

            $full_stars  = floor($average_rating);
            $partial_val = ($average_rating - $full_stars) * 100; // z. B. 0.3 → 30 %
            $empty_stars = 5 - ceil($average_rating);

            echo '<div class="ud-rating-summary">';
            echo '<div class="ud-rating-summary__row">';
            echo 'Bewertungen gesamt: <strong>' . esc_html($total_reviews) . '</strong>';
            echo '</div>';

            echo '<div class="ud-rating-summary__row">';
            echo '<span class="ud-rating-summary__label">Durchschnittliche Bewertung:</span>';
            echo '<span class="ud-rating-summary__value">' . esc_html(number_format($average_rating, 1, ',', '')) . '</span>';
            echo '<div class="ud-rating-summary__stars">';

            // Stern-SVG-Funktion
            function ud_rating_star_svg($fill = 100) {
                $id = uniqid('grad_');
                return '
	<svg class="ud-star" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
		<defs>
			<linearGradient id="' . $id . '">
				<stop offset="' . $fill . '%" stop-color="#fabb05"/>
				<stop offset="' . $fill . '%" stop-color="#dcdcdc"/>
			</linearGradient>
		</defs>
		<path class="ud-star__path"
			fill="url(#' . $id . ')"
			d="M30.17,33.76 L27.09,24.11 C26.96,23.70 27.11,23.24 27.46,22.99 L35.44,17.31 C35.89,16.99 36.00,16.37 35.68,15.92 C35.49,15.65 35.19,15.50 34.86,15.50 L25.08,15.50 C24.64,15.50 24.26,15.22 24.12,14.81 L20.95,4.87 C20.78,4.35 20.22,4.06 19.69,4.23 C19.38,4.32 19.14,4.57 19.04,4.87 L15.86,14.81 C15.73,15.22 15.35,15.50 14.91,15.50 L5.13,15.50 C4.58,15.50 4.13,15.94 4.13,16.50 C4.13,16.82 4.28,17.12 4.55,17.31 L12.53,22.99 C12.88,23.24 13.04,23.70 12.90,24.11 L9.83,33.76 C9.66,34.29 9.95,34.85 10.47,35.02 C10.79,35.12 11.12,35.06 11.38,34.86 L19.39,28.85 C19.75,28.58 20.24,28.58 20.60,28.85 L28.62,34.86 C29.06,35.20 29.68,35.11 30.02,34.67 C30.21,34.41 30.27,34.07 30.17,33.76 Z"/>
	</svg>';
            }

            // volle Sterne
            for ($i = 0; $i < $full_stars; $i++) echo ud_rating_star_svg(100);
            // teilgefüllter Stern
            if ($partial_val > 0) echo ud_rating_star_svg($partial_val);
            // leere Sterne
            for ($i = 0; $i < $empty_stars; $i++) echo ud_rating_star_svg(0);

            echo '</div>'; // .ud-rating-summary__stars
            echo '</div>'; // .ud-rating-summary__row
            echo '</div>'; // .ud-rating-summary
            ?>















            <?php
            /* =============================================================== *\
               Tabelle mit den einzelnen Bewertungen
            \* =============================================================== */
            ?>

            <h2><?php esc_html_e('Einzelne Bewertungen', 'rating-block-ud'); ?></h2>

            <?php
            global $wpdb;
            $table = $wpdb->prefix . 'ud_rating_reviews';

            $filter_rating = isset($_GET['filter_rating']) ? (int) $_GET['filter_rating'] : 0;
            $filter_period = isset($_GET['filter_period']) ? sanitize_text_field($_GET['filter_period']) : '';

            // 🔹 Filter + Alle löschen in einer Zeile
            echo '<form method="get" style="display:flex; align-items:center; gap:6px; justify-content:space-between; margin-bottom:1em;">';
            echo '<div>';
            echo '<input type="hidden" name="page" value="ud_rating_settings">';
            echo '<input type="hidden" name="tab" value="tab-reviews">';
            echo '<select name="filter_rating" style="margin-right:6px;" onchange="this.form.submit()">';

            echo '<option value="0">' . esc_html__('Alle Bewertungen', 'rating-block-ud') . '</option>';
            for ($i = 5; $i >= 1; $i--) {
                $selected = $filter_rating === $i ? 'selected' : '';
                echo "<option value='{$i}' {$selected}>{$i} ★</option>";
            }
            echo '</select>';

            echo '<select name="filter_period" style="margin-right:6px;" onchange="this.form.submit()">';
            echo '<option value="">' . esc_html__('Gesamter Zeitraum', 'rating-block-ud') . '</option>';
            echo '<option value="7d"' . selected($filter_period, '7d', false) . '>Letzte 7 Tage</option>';
            echo '<option value="30d"' . selected($filter_period, '30d', false) . '>Letzte 30 Tage</option>';
            echo '</select>';

            submit_button(__('Filtern', 'rating-block-ud'), 'secondary', '', false);
            echo '</div>';

            // 🔹 Button rechts in derselben Zeile
            $delete_url = esc_url(admin_url('options-general.php?page=ud_rating_settings&tab=tab-reviews&delete_all=1'));
            echo '<div style="margin-left:auto;">';
            echo '<a class="button button-link-delete" href="' . $delete_url . '" class="button button-secondary" onclick="return confirm(\'Bist du sicher, dass du alle Bewertungen dauerhaft löschen möchtest? Dieser Vorgang kann nicht rückgängig gemacht werden!\');">';
            echo esc_html__('Alle Bewertungen löschen', 'rating-block-ud');
            echo '</a>';
            echo '</div>';

            echo '</form>';


            $where = [];
            if ($filter_rating > 0) $where[] = $wpdb->prepare('rating = %d', $filter_rating);
            if ($filter_period === '7d') $where[] = "created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            elseif ($filter_period === '30d') $where[] = "created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            $sql_where = $where ? 'WHERE ' . implode(' AND ', $where) : '';
            $reviews = $wpdb->get_results("SELECT * FROM $table {$sql_where} ORDER BY created_at DESC LIMIT 500");

            if ($reviews) {
                echo '<table class="widefat fixed striped">';
                echo '<thead><tr><th>Bewertung</th><th>Kommentar</th><th>Nutzer-ID</th><th>Datum & Uhrzeit</th><th>Aktion</th></tr></thead><tbody>';
                foreach ($reviews as $review) {
                    $rating = (int)$review->rating;
                    $stars = '';
                    for ($i = 1; $i <= 5; $i++) {
                        $stars .= '<span style="color:' . ($i <= $rating ? '#f1c40f' : '#ccc') . ';font-size:16px;">★</span>';
                    }
                    echo '<tr>';
                    echo '<td>' . $stars . '</td>';
                    echo '<td style="max-width:300px;white-space:pre-wrap;">' . esc_html($review->comment ?? '') . '</td>';
                    $short_id = substr($review->ip_address, 0, 8) . '…';
                    echo '<td title="' . esc_attr($review->ip_address) . '">' . esc_html($short_id) . '</td>';
                    echo '<td>' . esc_html(date_i18n('d.m.Y, H:i', strtotime($review->created_at))) . '</td>';
                    echo '<td><a href="' . esc_url(admin_url('options-general.php?page=ud_rating_settings&delete=' . intval($review->id))) . '" class="button delete-rating button-link-delete">' . esc_html__('Löschen', 'rating-block-ud') . '</a></td>';
                    echo '</tr>';
                }
                echo '</tbody></table>';
            } else {
                echo '<p>' . esc_html__('Keine Bewertungen gefunden.', 'rating-block-ud') . '</p>';
            }
            ?>
        </section>
    </div>
<?php
}

/* =============================================================== *\
   🔹 Bewertungen löschen (Einzeln oder alle)
\* =============================================================== */
add_action('admin_init', function () {
    if (!isset($_GET['page']) || $_GET['page'] !== 'ud_rating_settings') return;
    if (!current_user_can('manage_options')) return;

    global $wpdb;
    $table = $wpdb->prefix . 'ud_rating_reviews';

    if (isset($_GET['delete'])) {
        $wpdb->delete($table, ['id' => intval($_GET['delete'])], ['%d']);
        wp_safe_redirect(admin_url('options-general.php?page=ud_rating_settings&tab=tab-reviews'));
        exit;
    }

    if (isset($_GET['delete_all']) && $_GET['delete_all'] === '1') {
        $wpdb->query("TRUNCATE TABLE {$table}");
        wp_safe_redirect(admin_url('options-general.php?page=ud_rating_settings&tab=tab-reviews'));
        exit;
    }
});


add_action('admin_footer', function () {
    $screen = get_current_screen();
    if (!$screen || $screen->id !== 'settings_page_ud_rating_settings') {
        return;
    }
?>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const buttons = document.querySelectorAll('.ud-tab-button');
            const tabs = document.querySelectorAll('.ud-tab-content');
            const params = new URLSearchParams(window.location.search);
            const active = params.get('tab') || 'tab-settings';

            buttons.forEach(b => b.classList.toggle('is-active', b.dataset.tab === active));
            tabs.forEach(t => t.classList.toggle('is-active', t.id === active));

            buttons.forEach(btn => {
                btn.addEventListener('click', () => {
                    const target = btn.dataset.tab;
                    buttons.forEach(b => b.classList.remove('is-active'));
                    tabs.forEach(t => t.classList.remove('is-active'));
                    btn.classList.add('is-active');
                    document.getElementById(target)?.classList.add('is-active');
                });
            });
        });
    </script>
<?php
});
