<?php

/**
 * Admin-Einstellungsseite für den Rating-Block
 */

defined('ABSPATH') || exit;

/**
 * 🌟 Ulrich.digital Fallback-Texte (globale Defaults)
 * Diese Werte werden automatisch im Frontend verwendet,
 * falls kein eigener Google-Link hinterlegt ist (50/50-Logik).
 */



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
	   🧩 Allgemeine Einstellungen
	\* =============================================================== */
    add_settings_section(
        'ud_rating_section_general',
        __('Allgemeine Einstellungen', 'rating-block-ud'),
        '__return_false',
        'ud_rating_settings'
    );

    register_setting('ud_rating_settings_group', 'ud_rating_dev_mode', [
        'type'              => 'boolean',
        'default'           => false,
        'sanitize_callback' => fn($v) => (bool) $v,
    ]);
    add_settings_field(
        'ud_rating_dev_mode',
        __('Entwicklermodus aktivieren', 'rating-block-ud'),
        function () {
            $value = get_option('ud_rating_dev_mode', false);
            echo '<label><input type="checkbox" name="ud_rating_dev_mode" value="1" ' . checked($value, true, false) . '> ';
            echo esc_html__('Erlaubt unbegrenzte Bewertungen zu Testzwecken', 'rating-block-ud') . '</label>';
        },
        'ud_rating_settings',
        'ud_rating_section_general'
    );

    register_setting('ud_rating_settings_group', 'ud_rating_start_date', [
        'type'              => 'string',
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    add_settings_field(
        'ud_rating_start_date',
        __('Startdatum der Anzeige', 'rating-block-ud'),
        function () {
            $value = get_option('ud_rating_start_date', '');
            echo '<input type="datetime-local" name="ud_rating_start_date" value="' . esc_attr($value) . '">';
        },
        'ud_rating_settings',
        'ud_rating_section_general'
    );

    register_setting('ud_rating_settings_group', 'ud_rating_end_date', [
        'type'              => 'string',
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    add_settings_field(
        'ud_rating_end_date',
        __('Enddatum der Anzeige', 'rating-block-ud'),
        function () {
            $value = get_option('ud_rating_end_date', '');
            echo '<input type="datetime-local" name="ud_rating_end_date" value="' . esc_attr($value) . '">';
        },
        'ud_rating_settings',
        'ud_rating_section_general'
    );

    register_setting('ud_rating_settings_group', 'ud_rating_max_displays', [
        'type'              => 'integer',
        'default'           => 3,
        'sanitize_callback' => 'absint',
    ]);
    add_settings_field(
        'ud_rating_max_displays',
        __('Maximale Anzeigen pro Nutzer', 'rating-block-ud'),
        function () {
            $value = get_option('ud_rating_max_displays', 3);
            echo '<input type="number" name="ud_rating_max_displays" min="1" max="10" value="' . esc_attr($value) . '">';
        },
        'ud_rating_settings',
        'ud_rating_section_general'
    );

    /* =============================================================== *\
   🎉 Darstellung & Verhalten
\* =============================================================== */
    add_settings_section(
        'ud_rating_section_display',
        __('Darstellung & Verhalten', 'rating-block-ud'),
        '__return_false',
        'ud_rating_settings'
    );



    // Zeitverzögerung für Einblendung
    register_setting('ud_rating_settings_group', 'ud_rating_display_delay', [
        'type'              => 'integer',
        'default'           => 0,
        'sanitize_callback' => 'absint',
    ]);
    add_settings_field(
        'ud_rating_display_delay',
        __('Verzögerung der Einblendung', 'rating-block-ud'),
        function () {
            $value = get_option('ud_rating_display_delay', 0);
            echo '<input type="number" min="0" step="1" name="ud_rating_display_delay" value="' . esc_attr($value) . '" style="width:100px;">';
            echo '<p class="description">' . esc_html__('Zeit in Sekunden, bis der Block angezeigt wird (0 = sofort).', 'rating-block-ud') . '</p>';
        },
        'ud_rating_settings',
        'ud_rating_section_display'
    );

    // Eigenes CSS
    register_setting('ud_rating_settings_group', 'ud_rating_custom_css', [
        'type'              => 'string',
        'default'           => '',
        'sanitize_callback' => 'wp_strip_all_tags', // optional, du kannst sanitize_textarea_field nutzen
    ]);
    add_settings_field(
        'ud_rating_custom_css',
        __('Eigenes CSS', 'rating-block-ud'),
        function () {
            $value = get_option('ud_rating_custom_css', '');
            echo '<textarea name="ud_rating_custom_css" rows="6" cols="50" class="large-text code" style="font-family:monospace;">' . esc_textarea($value) . '</textarea>';
            echo '<p class="description">' . esc_html__('Hier kannst du eigene CSS-Anpassungen vornehmen, die im Frontend automatisch eingebunden werden.', 'rating-block-ud') . '</p>';
        },
        'ud_rating_settings',
        'ud_rating_section_display'
    );


    /* =============================================================== *\
	   💬 Texte und Beschriftungen
	\* =============================================================== */
    add_settings_section(
        'ud_rating_section_texts',
        __('Texte und Beschriftungen', 'rating-block-ud'),
        '__return_false',
        'ud_rating_settings'
    );

    $text_fields = [
        'ud_rating_text_question' => [
            'label'   => __('Frage an Nutzer', 'rating-block-ud'),
            'default' => 'Gefällt dir der neue Auftritt?',
        ],
        'ud_rating_text_thanks' => [
            'label'   => __('Dankestext nach Bewertung', 'rating-block-ud'),
            'default' => 'Danke für dein Feedback!',
        ],
        'ud_rating_text_comment_placeholder' => [
            'label'   => __('Platzhalter für Kommentarfeld', 'rating-block-ud'),
            'default' => 'Möchtest du noch kurz etwas dazu sagen?',
        ],
        'ud_rating_text_after_comment' => [
            'label'   => __('Text nach Kommentar', 'rating-block-ud'),
            'default' => 'Dein Kommentar wurde gespeichert.',
        ],
        'ud_rating_text_comment_button' => [
            'label'   => __('Button-Text für Kommentar absenden', 'rating-block-ud'),
            'default' => 'Absenden',
        ],

    ];

    foreach ($text_fields as $option => $data) {
        register_setting('ud_rating_settings_group', $option, [
            'type'              => 'string',
            'default'           => $data['default'],
            'sanitize_callback' => 'sanitize_text_field',
        ]);

        add_settings_field(
            $option,
            esc_html($data['label']),
            function () use ($option, $data) {
                $value = get_option($option, $data['default']);
                echo '<input type="text" name="' . esc_attr($option) . '" value="' . esc_attr($value) . '" class="regular-text">';
            },
            'ud_rating_settings',
            'ud_rating_section_texts'
        );
    }



    /* =============================================================== *\
   ⭐ Bewertungslogik & Google-Link
\* =============================================================== */
    add_settings_section(
        'ud_rating_section_logic',
        __('Bewertungslogik & Google-Link', 'rating-block-ud'),
        '__return_false',
        'ud_rating_settings'
    );

    // 🔹 Mindestbewertung
    register_setting('ud_rating_settings_group', 'ud_rating_min_stars_for_google', [
        'type'              => 'integer',
        'default'           => 4,
        'sanitize_callback' => fn($v) => max(1, min(5, intval($v))),
    ]);
    add_settings_field(
        'ud_rating_min_stars_for_google',
        __('Mindestbewertung für Google-Link', 'rating-block-ud'),
        function () {
            $value = get_option('ud_rating_min_stars_for_google', 4);
            echo '<input type="range" name="ud_rating_min_stars_for_google" min="1" max="5" step="1" value="' . esc_attr($value) . '" oninput="this.nextElementSibling.value=this.value">';
            echo ' <output style="font-weight:600;">' . esc_html($value) . '</output>';
            echo '<p class="description">' . esc_html__('Nur wenn die abgegebene Bewertung mindestens diesen Wert erreicht, wird der Google-Link angezeigt.', 'rating-block-ud') . '</p>';
        },
        'ud_rating_settings',
        'ud_rating_section_logic'
    );

    // Confetti-Splash aktivieren
    register_setting('ud_rating_settings_group', 'ud_rating_enable_confetti', [
        'type'              => 'boolean',
        'default'           => false,
        'sanitize_callback' => fn($v) => (bool) $v,
    ]);
    add_settings_field(
        'ud_rating_enable_confetti',
        __('Confetti-Splash aktivieren', 'rating-block-ud'),
        function () {
            $value = get_option('ud_rating_enable_confetti', false);
            echo '<label><input type="checkbox" name="ud_rating_enable_confetti" value="1" ' . checked($value, true, false) . '> ';
            echo esc_html__('Zeigt beim erfolgreichen Absenden eine kurze Konfetti-Animation.', 'rating-block-ud') . '</label>';
        },
        'ud_rating_settings',
        'ud_rating_section_logic'
    );



    // 🔹 Google-Link (Unternehmen)
    register_setting('ud_rating_settings_group', 'ud_rating_google_link_company', [
        'type'              => 'string',
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ]);
    add_settings_field(
        'ud_rating_google_link_company',
        __('Google-Link', 'rating-block-ud'),
        function () {
            $value = get_option('ud_rating_google_link_company', '');
            echo '<input type="url" name="ud_rating_google_link_company" value="' . esc_attr($value) . '" class="regular-text" placeholder="https://g.page/.../review">';
            echo '<p class="description">Link für Kundenbewertungen – z. B. Google-Profil.</p>';
        },
        'ud_rating_settings',
        'ud_rating_section_logic'
    );

    // 🔹 Texte: Frage + Button
    $google_text_fields = [
        'ud_rating_text_google_company' => [
            'label'   => __('Frage zur Google-Bewertung', 'rating-block-ud'),
            'default' => 'Möchtest du deine Bewertung auf Google teilen?',
        ],
        'ud_rating_text_button_company' => [
            'label'   => __('Button-Text', 'rating-block-ud'),
            'default' => 'Jetzt auf Google bewerten',
        ],
    ];

    foreach ($google_text_fields as $option => $data) {
        register_setting('ud_rating_settings_group', $option, [
            'type'              => 'string',
            'default'           => $data['default'],
            'sanitize_callback' => 'sanitize_text_field',
        ]);

        add_settings_field(
            $option,
            esc_html($data['label']),
            function () use ($option, $data) {
                $value = get_option($option, $data['default']);
                echo '<input type="text" name="' . esc_attr($option) . '" value="' . esc_attr($value) . '" class="regular-text">';
            },
            'ud_rating_settings',
            'ud_rating_section_logic'
        );
    }

    // 🔹 Ulrich.Digital-Fallback-Texte (nur zur Anzeige, nicht editierbar)
    add_settings_field(
        'ud_rating_fallback_info',
        __('Einträge von ulrich.digital', 'rating-block-ud'),
        function () {
            echo '<input type="text" readonly class="regular-text" value="' . esc_attr(UD_RATING_FALLBACK_LINK) . '" style="opacity:.6;">';
            echo '<p class="description" style="margin-bottom:12px;">' . esc_html__('Fester Agentur-Link', 'rating-block-ud') . '</p>';

            echo '<input type="text" readonly class="regular-text" value="' . esc_attr(UD_RATING_FALLBACK_TEXT) . '" style="opacity:.6;">';
            echo '<p class="description" style="margin-bottom:12px;">' . esc_html__('Frage-Text für ulrich.digital', 'rating-block-ud') . '</p>';

            echo '<input type="text" readonly class="regular-text" value="' . esc_attr(UD_RATING_FALLBACK_BUTTON) . '" style="opacity:.6;">';
            echo '<p class="description">' . esc_html__('Button-Text für ulrich.digital', 'rating-block-ud') . '</p>';
        },
        'ud_rating_settings',
        'ud_rating_section_logic'
    );


    /* =============================================================== *\
   🔹 Bewertungen löschen
\* =============================================================== */


    /**
     * Rendering der Einstellungsseite.
     */
    function ud_rating_render_settings_page() {
?>
        <div class="wrap ud-rating-settings-wrap">
            <h1><?php esc_html_e('UD Rating Block', 'rating-block-ud'); ?></h1>

            <nav class="ud-rating-tabs">
                <button class="ud-tab-button is-active" data-tab="tab-settings"><?php esc_html_e('Einstellungen', 'rating-block-ud'); ?></button>
                <button class="ud-tab-button" data-tab="tab-reviews"><?php esc_html_e('Bewertungen', 'rating-block-ud'); ?></button>
            </nav>

            <!-- 🧩 Einstellungen -->
            <section id="tab-settings" class="ud-tab-content is-active">
                <form method="post" action="options.php">
                    <?php
                    settings_fields('ud_rating_settings_group');
                    do_settings_sections('ud_rating_settings');
                    submit_button(__('Änderungen speichern', 'rating-block-ud'));
                    ?>
                </form>
            </section>

            <!-- 💬 Bewertungen -->
            <section id="tab-reviews" class="ud-tab-content">
                <h2><?php esc_html_e('Erhaltene Bewertungen', 'rating-block-ud'); ?></h2>
                <?php
                global $wpdb;
                $table = $wpdb->prefix . 'ud_rating_reviews';
                $reviews = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC LIMIT 500");

                // Filter UI
                $filter_rating = isset($_GET['filter_rating']) ? (int) $_GET['filter_rating'] : 0;
                $filter_period = isset($_GET['filter_period']) ? sanitize_text_field($_GET['filter_period']) : '';

                echo '<form method="get" style="margin-bottom:1em;">';
                echo '<input type="hidden" name="page" value="ud_rating_settings">';
                echo '<input type="hidden" name="tab" value="tab-reviews">';
                echo '<select name="filter_rating" style="margin-right:6px;">';
                echo '<option value="0">' . esc_html__('Alle Bewertungen', 'rating-block-ud') . '</option>';
                for ($i = 5; $i >= 1; $i--) {
                    $selected = $filter_rating === $i ? 'selected' : '';
                    echo "<option value='{$i}' {$selected}>{$i} ★</option>";
                }
                echo '</select>';

                echo '<select name="filter_period" style="margin-right:6px;">';
                echo '<option value="">' . esc_html__('Gesamter Zeitraum', 'rating-block-ud') . '</option>';
                echo '<option value="7d"'  . selected($filter_period, '7d', false)  . '>Letzte 7 Tage</option>';
                echo '<option value="30d"' . selected($filter_period, '30d', false) . '>Letzte 30 Tage</option>';
                echo '</select>';

                submit_button(__('Filtern', 'rating-block-ud'), 'secondary', '', false);
                echo '</form>';

                // SQL-Filter anwenden
                $where = [];
                if ($filter_rating > 0) {
                    $where[] = $wpdb->prepare('rating = %d', $filter_rating);
                }
                if ($filter_period === '7d') {
                    $where[] = "created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                } elseif ($filter_period === '30d') {
                    $where[] = "created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
                }
                $sql_where = $where ? 'WHERE ' . implode(' AND ', $where) : '';
                $reviews = $wpdb->get_results("SELECT * FROM $table {$sql_where} ORDER BY created_at DESC LIMIT 500");


                if ($reviews) {
                    echo '<table class="widefat fixed striped">';
                    echo '<thead><tr><th>Bewertung</th><th>Kommentar</th><th>Nutzer-ID</th><th>Datum & Uhrzeit</th><th>Aktion</th></tr></thead><tbody>';
                    foreach ($reviews as $review) {
                        $rating = (int) $review->rating;
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
                        echo '<td><a href="' . esc_url(admin_url('options-general.php?page=ud_rating_settings&delete=' . intval($review->id))) . '" class="button delete-rating">' . esc_html__('Löschen', 'rating-block-ud') . '</a></td>';
                        echo '</tr>';
                    }
                    echo '</tbody></table>';
                    echo '<p style="margin-top:15px;">';
                    echo '<a href="' . esc_url(
                        admin_url('options-general.php?page=ud_rating_settings&tab=tab-reviews&delete_all=1')
                    ) . '" ';
                    echo 'class="button button-secondary delete-all-ratings" ';
                    echo 'onclick="return confirm(\'Bist du sicher, dass du alle Bewertungen dauerhaft löschen möchtest? Dieser Vorgang kann nicht rückgängig gemacht werden!\');">';
                    echo '🗑️ ' . esc_html__('Alle Bewertungen löschen', 'rating-block-ud');
                    echo '</a>';
                    echo '</p>';
                } else {
                    echo '<p>' . esc_html__('Keine Bewertungen gefunden.', 'rating-block-ud') . '</p>';
                }
                ?>
            </section>
        </div>
    <?php
    }


    /**
     * Tabs JS
     */
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
});



/* =============================================================== *\
   🔹 Bewertungen löschen
\* =============================================================== */
/**
 * 🔹 Bewertungen löschen (Einzeln oder alle)
 */
add_action('admin_init', function () {
    if (!isset($_GET['page']) || $_GET['page'] !== 'ud_rating_settings') {
        return;
    }

    if (!current_user_can('manage_options')) {
        return;
    }

    global $wpdb;
    $table = $wpdb->prefix . 'ud_rating_reviews';

    // Einzelne Bewertung löschen
    if (isset($_GET['delete'])) {
        $id = intval($_GET['delete']);
        $wpdb->delete($table, ['id' => $id], ['%d']);

        // 🔄 Nach Löschung zurück zu "Bewertungen"-Tab
        wp_safe_redirect(
            admin_url('options-general.php?page=ud_rating_settings&tab=tab-reviews')
        );
        exit;
    }

    // Alle Bewertungen löschen (nach Sicherheitsabfrage)
    if (isset($_GET['delete_all']) && $_GET['delete_all'] === '1') {
        $wpdb->query("TRUNCATE TABLE {$table}");

        // 🔄 Nach Löschung zurück zu "Bewertungen"-Tab
        wp_safe_redirect(
            admin_url('options-general.php?page=ud_rating_settings&tab=tab-reviews')
        );
        exit;
    }
});
