<?php
if (!defined('ABSPATH')) exit;

/**
 * Maak de database-tabel aan voor job alert inschrijvingen.
 */
function fn_job_alerts_create_table(): void {
    global $wpdb;
    $table   = $wpdb->prefix . 'fn_job_alerts';
    $charset = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        voornaam varchar(100) NOT NULL DEFAULT '',
        email varchar(200) NOT NULL DEFAULT '',
        sectors longtext NOT NULL DEFAULT '',
        work_types longtext NOT NULL DEFAULT '',
        location varchar(200) NOT NULL DEFAULT '',
        created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        last_sent datetime NULL DEFAULT NULL,
        unsubscribe_token varchar(64) NOT NULL DEFAULT '',
        active tinyint(1) NOT NULL DEFAULT 1,
        PRIMARY KEY (id),
        UNIQUE KEY email (email)
    ) $charset;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}

add_action('init', function () {
    if (!get_option('fn_job_alerts_table_v1')) {
        fn_job_alerts_create_table();
        update_option('fn_job_alerts_table_v1', true);
    }
});

/**
 * Shortcode: [fondsen-job-alerts]
 */
add_shortcode('fondsen-job-alerts', 'fn_job_alerts_shortcode');

function fn_job_alerts_shortcode(): string {
    global $wpdb;
    $table   = $wpdb->prefix . 'fn_job_alerts';
    $errors  = [];
    $success = false;

    if (
        $_SERVER['REQUEST_METHOD'] === 'POST' &&
        isset($_POST['fn_ja_nonce']) &&
        wp_verify_nonce($_POST['fn_ja_nonce'], 'fn_job_alerts')
    ) {
        $voornaam   = sanitize_text_field($_POST['voornaam']   ?? '');
        $email      = sanitize_email($_POST['email']           ?? '');
        $sectors    = array_map('sanitize_title', (array) ($_POST['sectors']    ?? []));
        $work_types = array_map('sanitize_title', (array) ($_POST['work_types'] ?? []));
        $location   = sanitize_text_field($_POST['location']   ?? '');

        if (!$voornaam)        $errors[] = 'Vul je voornaam in.';
        if (!is_email($email)) $errors[] = 'Vul een geldig e-mailadres in.';
        if (empty($sectors))   $errors[] = 'Kies minimaal één sector.';

        if (empty($errors)) {
            $token    = wp_generate_password(32, false, false);
            $existing = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$table} WHERE email = %s", $email
            ));

            if ($existing) {
                $wpdb->update(
                    $table,
                    [
                        'voornaam'   => $voornaam,
                        'sectors'    => wp_json_encode($sectors),
                        'work_types' => wp_json_encode($work_types),
                        'location'   => $location,
                        'active'     => 1,
                    ],
                    ['email' => $email],
                    ['%s', '%s', '%s', '%s', '%d'],
                    ['%s']
                );
            } else {
                $wpdb->insert(
                    $table,
                    [
                        'voornaam'          => $voornaam,
                        'email'             => $email,
                        'sectors'           => wp_json_encode($sectors),
                        'work_types'        => wp_json_encode($work_types),
                        'location'          => $location,
                        'unsubscribe_token' => $token,
                        'active'            => 1,
                    ],
                    ['%s', '%s', '%s', '%s', '%s', '%s', '%d']
                );
            }

            fn_ac_subscribe_job_alert($voornaam, $email, $sectors);

            $sector_names = [];
            foreach ($sectors as $slug) {
                $term = get_term_by('slug', $slug, 'job_sector');
                if ($term) $sector_names[] = $term->name;
            }

            $prefs = implode(', ', $sector_names);
            if ($location) $prefs .= ' · ' . $location;

            $body  = "Hoi {$voornaam},\n\n";
            $body .= "Je job alert op Fondsen.org is ingesteld!\n\n";
            $body .= "Je ontvangt wekelijks de nieuwste vacatures voor:\n";
            foreach ($sector_names as $name) {
                $body .= "- {$name}\n";
            }
            if ($location) $body .= "\nLocatievoorkeur: {$location}";
            $body .= "\n\nWil je je voorkeuren wijzigen of je afmelden? Stuur een mail naar nieuwsbrief@fondsen.org.\n\nMet vriendelijke groet,\nFondsen.org";

            wp_mail($email, 'Je job alert is ingesteld — Fondsen.org', $body, [
                'From: Fondsen.org <nieuwsbrief@fondsen.org>',
            ]);

            $admin_email = get_option('admin_email');
            $admin_body  = "Nieuwe job alert aanmelding op Fondsen.org\n\nNaam: {$voornaam}\nE-mail: {$email}\nSectoren: {$prefs}";
            wp_mail($admin_email, 'Nieuwe job alert — Fondsen.org', $admin_body);

            $success = true;
        }
    }

    $sector_terms    = get_terms(['taxonomy' => 'job_sector',        'hide_empty' => false, 'orderby' => 'name']) ?: [];
    $work_type_terms = get_terms(['taxonomy' => 'job_listing_type',  'hide_empty' => false, 'orderby' => 'name']) ?: [];
    if (is_wp_error($sector_terms))    $sector_terms    = [];
    if (is_wp_error($work_type_terms)) $work_type_terms = [];

    ob_start();

    if ($success): ?>

    <div class="fn-ja-notice fn-ja-notice--success">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M173.66,98.34a8,8,0,0,1,0,11.32l-56,56a8,8,0,0,1-11.32,0l-24-24a8,8,0,0,1,11.32-11.32L112,148.69l50.34-50.35A8,8,0,0,1,173.66,98.34ZM232,128A104,104,0,1,1,128,24,104.11,104.11,0,0,1,232,128Zm-16,0a88,88,0,1,0-88,88A88.1,88.1,0,0,0,216,128Z"/></svg>
        <div>
            <strong>Je job alert is ingesteld!</strong>
            <p>Je ontvangt wekelijks de nieuwste vacatures in jouw gekozen sectoren. Check ook je spammap.</p>
        </div>
    </div>

    <?php else: ?>

    <?php if (!empty($errors)): ?>
    <div class="fn-ja-notice fn-ja-notice--error">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M236.8,188.09,149.35,36.22a24.76,24.76,0,0,0-42.7,0L19.2,188.09a23.51,23.51,0,0,0,0,23.72A24.35,24.35,0,0,0,40.55,224h174.9a24.35,24.35,0,0,0,21.33-12.19A23.51,23.51,0,0,0,236.8,188.09ZM120,104a8,8,0,0,1,16,0v40a8,8,0,0,1-16,0Zm8,88a12,12,0,1,1,12-12A12,12,0,0,1,128,192Z"/></svg>
        <div>
            <strong>Controleer de volgende velden:</strong>
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?php echo esc_html($e); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php endif; ?>

    <div class="fn-ja">
        <div class="fn-ja__block">

            <header class="fn-ja__header">
                <h2 class="fn-ja__title">Job alert instellen</h2>
                <p class="fn-ja__subtitle">Ontvang wekelijks de nieuwste vacatures in jouw vakgebied direct in je inbox.</p>
            </header>

            <form method="post" class="fn-ja__form" novalidate>
                <?php wp_nonce_field('fn_job_alerts', 'fn_ja_nonce'); ?>

                <div class="fn-ja__fields">

                    <div class="fn-ja__field">
                        <label class="fn-ja__label" for="fn_ja_voornaam">Voornaam <span class="fn-ja__req">*</span></label>
                        <div class="fn-ja__input-wrap">
                            <svg class="fn-ja__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#0884CC" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            <input type="text" name="voornaam" id="fn_ja_voornaam" class="fn-ja__input"
                                   value="<?php echo esc_attr($_POST['voornaam'] ?? ''); ?>" required>
                        </div>
                    </div>

                    <div class="fn-ja__field">
                        <label class="fn-ja__label" for="fn_ja_email">E-mailadres <span class="fn-ja__req">*</span></label>
                        <div class="fn-ja__input-wrap">
                            <svg class="fn-ja__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#0884CC" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                            <input type="email" name="email" id="fn_ja_email" class="fn-ja__input"
                                   value="<?php echo esc_attr($_POST['email'] ?? ''); ?>" required>
                        </div>
                    </div>

                    <div class="fn-ja__field fn-ja__field--full">
                        <label class="fn-ja__label" for="fn_ja_sectors">Sectoren <span class="fn-ja__req">*</span></label>
                        <?php $selected_sectors = array_map('sanitize_title', (array) ($_POST['sectors'] ?? [])); ?>
                        <select name="sectors[]" id="fn_ja_sectors" class="js-fn-select" data-placeholder="Kies sectoren" multiple>
                            <?php foreach ($sector_terms as $term): ?>
                            <option value="<?php echo esc_attr($term->slug); ?>"
                                <?php selected(in_array($term->slug, $selected_sectors)); ?>>
                                <?php echo esc_html($term->name); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <?php if (!empty($work_type_terms)): ?>
                    <div class="fn-ja__field fn-ja__field--full">
                        <label class="fn-ja__label" for="fn_ja_work_types">Werktype <span class="fn-ja__optional">(optioneel)</span></label>
                        <?php $selected_work_types = array_map('sanitize_title', (array) ($_POST['work_types'] ?? [])); ?>
                        <select name="work_types[]" id="fn_ja_work_types" class="js-fn-select" data-placeholder="Alle werktypes" multiple>
                            <?php foreach ($work_type_terms as $term): ?>
                            <option value="<?php echo esc_attr($term->slug); ?>"
                                <?php selected(in_array($term->slug, $selected_work_types)); ?>>
                                <?php echo esc_html($term->name); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>

                    <div class="fn-ja__field fn-ja__field--full">
                        <label class="fn-ja__label" for="fn_ja_location">Locatie / regio <span class="fn-ja__optional">(optioneel)</span></label>
                        <div class="fn-ja__input-wrap">
                            <svg class="fn-ja__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#0884CC" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                            <input type="text" name="location" id="fn_ja_location" class="fn-ja__input"
                                   placeholder="bijv. Amsterdam, Utrecht of Landelijk"
                                   value="<?php echo esc_attr($_POST['location'] ?? ''); ?>">
                        </div>
                    </div>

                </div>

                <div class="fn-ja-active-filters" id="fn_ja_active_filters" aria-live="polite"></div>

                <footer class="fn-ja__footer">
                    <button type="submit" class="fn-ja__submit">Alert instellen</button>
                </footer>

            </form>
        </div>
    </div>

    <script>
    (function () {
        var closeAll = function () {
            document.querySelectorAll('.fn-select.active').forEach(function (el) {
                el.classList.remove('active');
                var s = el.querySelector('.fn-search-input');
                if (s) { s.value = ''; el.querySelectorAll('.fn-option').forEach(function (o) { o.style.display = ''; }); }
            });
        };

        var buildSelect = function (select) {
            if (select.classList.contains('fn-hidden-select')) return;
            var placeholder = select.dataset.placeholder || 'Selecteer';

            var wrap = document.createElement('div');
            wrap.className = 'fn-select-wrap';
            select.parentNode.insertBefore(wrap, select);
            wrap.appendChild(select);
            select.classList.add('fn-hidden-select');

            var root = document.createElement('div');
            root.className = 'fn-select';

            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'fn-select-btn';
            btn.innerHTML = '<span class="fn-btn-content"><span class="fn-placeholder">' + placeholder + '</span></span><span class="fn-actions"><button type="button" class="fn-clear" aria-label="Wis selectie">×</button><span class="fn-chev" aria-hidden="true"></span></span>';

            var clearBtn = btn.querySelector('.fn-clear');
            clearBtn.addEventListener('click', function (e) {
                e.stopPropagation(); e.preventDefault();
                Array.from(select.options).forEach(function (o) { o.selected = false; });
                renderState();
            });

            var list = document.createElement('div');
            list.className = 'fn-options';
            list.setAttribute('role', 'listbox');
            list.setAttribute('aria-multiselectable', 'true');

            var searchWrap = document.createElement('div');
            searchWrap.className = 'fn-search';
            searchWrap.innerHTML = '<input type="text" class="fn-search-input" placeholder="Zoeken...">';
            var searchInput = searchWrap.querySelector('.fn-search-input');
            list.appendChild(searchWrap);

            var optionRows = [];
            Array.from(select.options).forEach(function (opt) {
                var row = document.createElement('div');
                row.className = 'fn-option';
                row.dataset.value = opt.value;
                row.setAttribute('role', 'option');
                row.innerHTML = '<span class="fn-option-text"></span>';
                row.querySelector('.fn-option-text').textContent = opt.textContent.trim();

                var syncSelected = function () {
                    row.classList.toggle('is-selected', opt.selected);
                    row.setAttribute('aria-selected', opt.selected ? 'true' : 'false');
                };
                syncSelected();
                row.addEventListener('click', function (e) { e.preventDefault(); opt.selected = !opt.selected; renderState(); });
                optionRows.push({ opt: opt, row: row, syncSelected: syncSelected });
                list.appendChild(row);
            });

            searchInput.addEventListener('input', function () {
                var term = searchInput.value.trim().toLowerCase();
                optionRows.forEach(function (item) {
                    item.row.style.display = item.opt.textContent.toLowerCase().includes(term) ? '' : 'none';
                });
            });
            searchInput.addEventListener('click', function (e) { e.stopPropagation(); });
            searchInput.addEventListener('keydown', function (e) { e.stopPropagation(); });

            var activeFiltersContainer = document.getElementById('fn_ja_active_filters');

            var renderChips = function () {
                if (!activeFiltersContainer) return;
                // Only render chips for the sectors select
                if (select.id !== 'fn_ja_sectors') return;
                var existing = activeFiltersContainer.querySelectorAll('.fn-ja-chip[data-select="' + select.id + '"]');
                existing.forEach(function (c) { c.remove(); });
                Array.from(select.options).filter(function (o) { return o.selected; }).forEach(function (opt) {
                    var chip = document.createElement('span');
                    chip.className = 'fn-ja-chip';
                    chip.dataset.select = select.id;
                    var text = document.createElement('span');
                    text.textContent = opt.textContent.trim();
                    var rm = document.createElement('button');
                    rm.type = 'button'; rm.className = 'fn-ja-chip__remove';
                    rm.setAttribute('aria-label', 'Verwijder ' + opt.textContent.trim());
                    rm.textContent = '×';
                    rm.addEventListener('click', function (e) { e.preventDefault(); opt.selected = false; renderState(); });
                    chip.appendChild(text); chip.appendChild(rm);
                    activeFiltersContainer.appendChild(chip);
                });
                activeFiltersContainer.classList.toggle('has-items',
                    activeFiltersContainer.querySelectorAll('.fn-ja-chip').length > 0
                );
            };

            var renderState = function () {
                optionRows.forEach(function (item) { item.syncSelected(); });
                var selected = Array.from(select.options).filter(function (o) { return o.selected; });
                clearBtn.style.display = selected.length ? 'inline-flex' : 'none';
                // Update button label
                var content = btn.querySelector('.fn-btn-content');
                var ph = btn.querySelector('.fn-placeholder');
                if (selected.length === 0) {
                    ph.textContent = placeholder;
                } else if (selected.length === 1) {
                    ph.textContent = selected[0].textContent.trim();
                } else {
                    ph.textContent = selected.length + ' geselecteerd';
                }
                renderChips();
            };

            renderState();

            btn.addEventListener('click', function (e) {
                if (e.target.closest('.fn-clear')) return;
                e.preventDefault();
                var wasOpen = root.classList.contains('active');
                closeAll();
                if (!wasOpen) {
                    root.classList.add('active');
                    searchInput.value = '';
                    optionRows.forEach(function (item) { item.row.style.display = ''; });
                    window.setTimeout(function () { searchInput.focus(); }, 10);
                }
            });

            root.appendChild(btn);
            root.appendChild(list);
            wrap.appendChild(root);
        };

        var init = function () {
            document.querySelectorAll('.js-fn-select').forEach(buildSelect);
            document.addEventListener('click', function (e) { if (!e.target.closest('.fn-select')) closeAll(); });
            document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeAll(); });
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
        } else {
            init();
        }
    })();
    </script>

    <?php endif;

    return ob_get_clean();
}
