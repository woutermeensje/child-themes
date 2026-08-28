<?php
if (!defined('ABSPATH')) exit;

/**
 * Admin overview for Verhuisteam.nl shortcodes.
 *
 * Shows the available theme shortcodes in WP Admin under Tools.
 */

add_action('admin_menu', function (): void {
    add_management_page(
        'Verhuisteam shortcodes',
        'Verhuisteam shortcodes',
        'edit_pages',
        'verhuisteam-shortcodes-overview',
        'si_render_shortcodes_overview_admin_page'
    );
});

function si_shortcodes_overview_platform_categories(): array {
    if (function_exists('si_platform_opdracht_categories')) {
        return si_platform_opdracht_categories();
    }

    return [
        'vertalers'        => 'Vertalers',
        'online-marketing' => 'Online marketing',
        'office'           => 'Office',
        'logistiek'        => 'Logistiek',
        'creative'         => 'Creative',
        'werkstudent'      => 'Werkstudent',
        'freelance'        => 'Freelance',
    ];
}

function si_shortcodes_overview_category_shortcodes(): array {
    $rows = [
        [
            'label'     => 'Alle platform-opdrachtpagina\'s',
            'shortcode' => '[platform_opdrachten_paginas]',
            'note'      => 'Toont alle pagina\'s waarbij "Platform opdrachten" is aangevinkt.',
        ],
        [
            'label'     => 'Bekijk ook-blok',
            'shortcode' => '[platform_opdrachten_bekijk_ook]',
            'note'      => 'Compact blok zonder zoekfunctie, standaard maximaal 12 items.',
        ],
    ];

    $shortcode_map = [
        'vertalers'        => 'platform_opdrachten_vertalers',
        'online-marketing' => 'platform_opdrachten_online_marketing',
        'office'           => 'platform_opdrachten_office',
        'logistiek'        => 'platform_opdrachten_logistiek',
        'creative'         => 'platform_opdrachten_creative',
        'werkstudent'      => 'platform_opdrachten_werkstudent',
        'freelance'        => 'platform_opdrachten_freelance',
    ];

    foreach (si_shortcodes_overview_platform_categories() as $slug => $label) {
        if (!isset($shortcode_map[$slug])) {
            continue;
        }

        $rows[] = [
            'label'     => $label,
            'shortcode' => '[' . $shortcode_map[$slug] . ']',
            'note'      => 'Toont alleen pagina\'s in de categorie "' . $label . '".',
        ];
    }

    return $rows;
}

function si_shortcodes_overview_attribute_examples(): array {
    $rows = [];

    foreach (si_shortcodes_overview_platform_categories() as $slug => $label) {
        $rows[] = [
            'label'     => $label,
            'shortcode' => '[platform_opdrachten_paginas category="' . $slug . '"]',
            'note'      => 'Alternatief voor de losse categorie-shortcode.',
        ];
    }

    return $rows;
}

function si_shortcodes_overview_other_shortcodes(): array {
    return [
        [
            'label'     => 'Laatste opdrachten',
            'shortcode' => '[si_latest_opdrachten]',
            'note'      => 'Toont recente opdrachten vanuit een JSON-feed.',
        ],
        [
            'label'     => 'Informatie aanvragen',
            'shortcode' => '[si_informatie_aanvragen]',
            'note'      => 'Volledig informatieaanvraagformulier.',
        ],
        [
            'label'     => 'Informatie aanvragen compact',
            'shortcode' => '[si_informatie_aanvragen_compact]',
            'note'      => 'Compact informatieaanvraagformulier.',
        ],
        [
            'label'     => 'Opdracht plaatsen',
            'shortcode' => '[si_opdracht_plaatsen]',
            'note'      => 'Formulier om een opdracht te plaatsen.',
        ],
        [
            'label'     => 'Vacature plaatsen',
            'shortcode' => '[vacature-plaatsen]',
            'note'      => 'Formulier om een vacature te plaatsen.',
        ],
        [
            'label'     => 'Tarieven',
            'shortcode' => '[si_tarieven]',
            'note'      => 'Tarievenaanvraagformulier.',
        ],
    ];
}

function si_render_shortcodes_overview_table(array $rows): void {
    ?>
    <table class="widefat striped si-shortcodes-table">
        <thead>
            <tr>
                <th scope="col">Naam</th>
                <th scope="col">Shortcode</th>
                <th scope="col">Toelichting</th>
                <th scope="col">Actie</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row) : ?>
                <tr>
                    <td><?php echo esc_html($row['label']); ?></td>
                    <td><code><?php echo esc_html($row['shortcode']); ?></code></td>
                    <td><?php echo esc_html($row['note']); ?></td>
                    <td>
                        <button
                            type="button"
                            class="button button-secondary si-copy-shortcode"
                            data-shortcode="<?php echo esc_attr($row['shortcode']); ?>"
                        >
                            Kopieer
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php
}

function si_render_shortcodes_overview_admin_page(): void {
    if (!current_user_can('edit_pages')) {
        wp_die(esc_html__('Je hebt geen rechten om deze pagina te bekijken.', 'verhuisteam'));
    }

    ?>
    <div class="wrap si-shortcodes-overview">
        <h1>Verhuisteam shortcodes</h1>
        <p class="description">
            Overzicht van de shortcodes uit het Verhuisteam.nl child theme. Gebruik de kopieerknop om een shortcode snel in een pagina of blok te plakken.
        </p>

        <div class="notice notice-info inline">
            <p>
                Platform-opdrachtpagina's beheer je per pagina via de zijbalk <strong>Platform opdrachten</strong>.
                Vink daar de juiste categorieen aan, dan verschijnen ze automatisch in de categorie-shortcodes.
            </p>
        </div>

        <section class="si-shortcodes-section">
            <h2>Categorie-shortcodes</h2>
            <?php si_render_shortcodes_overview_table(si_shortcodes_overview_category_shortcodes()); ?>
        </section>

        <section class="si-shortcodes-section">
            <h2>Algemene shortcode met categorie-attribuut</h2>
            <?php si_render_shortcodes_overview_table(si_shortcodes_overview_attribute_examples()); ?>
        </section>

        <section class="si-shortcodes-section">
            <h2>Overige shortcodes</h2>
            <?php si_render_shortcodes_overview_table(si_shortcodes_overview_other_shortcodes()); ?>
        </section>
    </div>

    <style>
        .si-shortcodes-overview {
            max-width: 1120px;
        }

        .si-shortcodes-overview .description {
            max-width: 760px;
            font-size: 14px;
        }

        .si-shortcodes-section {
            margin-top: 28px;
        }

        .si-shortcodes-section h2 {
            margin-bottom: 10px;
        }

        .si-shortcodes-table code {
            display: inline-block;
            padding: 4px 7px;
            border: 1px solid #dcdcde;
            border-radius: 4px;
            background: #f6f7f7;
            color: #1d2327;
            font-size: 13px;
            white-space: nowrap;
        }

        .si-shortcodes-table th:nth-child(2),
        .si-shortcodes-table td:nth-child(2) {
            width: 320px;
        }

        .si-shortcodes-table th:last-child,
        .si-shortcodes-table td:last-child {
            width: 110px;
            text-align: right;
        }
    </style>

    <script>
    document.addEventListener('click', function (event) {
        var button = event.target.closest('.si-copy-shortcode');
        if (!button) return;

        var shortcode = button.getAttribute('data-shortcode') || '';
        var originalText = button.textContent;

        function setCopied() {
            button.textContent = 'Gekopieerd';
            window.setTimeout(function () {
                button.textContent = originalText;
            }, 1400);
        }

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(shortcode).then(setCopied);
            return;
        }

        var textarea = document.createElement('textarea');
        textarea.value = shortcode;
        textarea.setAttribute('readonly', 'readonly');
        textarea.style.position = 'absolute';
        textarea.style.left = '-9999px';
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        setCopied();
    });
    </script>
    <?php
}
