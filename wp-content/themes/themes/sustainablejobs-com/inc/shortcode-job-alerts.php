<?php
if (!defined('ABSPATH')) exit;

/**
 * Create the database table for job alert subscriptions.
 */
function sj_job_alerts_create_table(): void {
    global $wpdb;
    $table   = $wpdb->prefix . 'sj_job_alerts';
    $charset = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        voornaam varchar(100) NOT NULL DEFAULT '',
        email varchar(200) NOT NULL DEFAULT '',
        sectors longtext NOT NULL DEFAULT '',
        created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        active tinyint(1) NOT NULL DEFAULT 1,
        PRIMARY KEY (id),
        UNIQUE KEY email (email)
    ) $charset;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}

add_action('init', function () {
    if (!get_option('sj_job_alerts_table_v1')) {
        sj_job_alerts_create_table();
        update_option('sj_job_alerts_table_v1', true);
    }
});

/**
 * Shared anti-spam checks for all job alert forms.
 */
function sj_job_alerts_get_client_ip(): string {
    return sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'] ?? ''));
}

function sj_job_alerts_spam_fields(string $prefix): string {
    $honeypot_name = $prefix . '_website';
    $started_name  = $prefix . '_started_at';
    $honeypot_id   = wp_unique_id($honeypot_name . '_');

    ob_start();
    ?>
    <div style="position:absolute;left:-9999px;top:auto;width:1px;height:1px;overflow:hidden;" aria-hidden="true">
        <label for="<?php echo esc_attr($honeypot_id); ?>">Website</label>
        <input type="text" name="<?php echo esc_attr($honeypot_name); ?>" id="<?php echo esc_attr($honeypot_id); ?>" value="" tabindex="-1" autocomplete="off">
    </div>
    <input type="hidden" name="<?php echo esc_attr($started_name); ?>" value="<?php echo esc_attr((string) time()); ?>">
    <?php
    return ob_get_clean();
}

function sj_job_alerts_log_spam(string $context, string $reason): void {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log(sprintf('[SJ Job Alerts] Spam blocked (%s): %s', $context, $reason));
    }
}

function sj_job_alerts_is_rate_limited(string $context, string $email): bool {
    $ip        = sj_job_alerts_get_client_ip();
    $email_key = 'sj_ja_email_rl_' . md5($context . '|' . strtolower($email) . '|' . $ip);
    $ip_key    = 'sj_ja_ip_rl_' . md5($context . '|' . $ip);

    $email_count = (int) get_transient($email_key);
    if ($email_count >= 5) {
        sj_job_alerts_log_spam($context, 'rate limit email/ip');
        return true;
    }

    $ip_count = (int) get_transient($ip_key);
    if ($ip_count >= 30) {
        sj_job_alerts_log_spam($context, 'rate limit ip');
        return true;
    }

    set_transient($email_key, $email_count + 1, HOUR_IN_SECONDS);
    set_transient($ip_key, $ip_count + 1, HOUR_IN_SECONDS);

    return false;
}

function sj_job_alerts_akismet_flags_spam(string $context, string $voornaam, string $email, array $sectors): bool {
    if (
        !class_exists('Akismet') ||
        !method_exists('Akismet', 'get_api_key') ||
        !method_exists('Akismet', 'build_query') ||
        !method_exists('Akismet', 'http_post') ||
        !Akismet::get_api_key()
    ) {
        return false;
    }

    $request_uri = sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'] ?? ''));
    $permalink   = is_singular() ? get_permalink() : home_url($request_uri ?: '/');

    $request = [
        'blog'                 => get_option('home'),
        'user_ip'              => sj_job_alerts_get_client_ip(),
        'user_agent'           => sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'] ?? '')),
        'referrer'             => wp_get_referer() ?: '',
        'permalink'            => $permalink,
        'comment_type'         => 'contact-form',
        'comment_author'       => $voornaam,
        'comment_author_email' => $email,
        'comment_content'      => 'Job alert signup (' . $context . '): ' . implode(', ', $sectors),
    ];

    $response = Akismet::http_post(Akismet::build_query($request), 'comment-check');

    if (is_array($response) && isset($response[1]) && trim((string) $response[1]) === 'true') {
        sj_job_alerts_log_spam($context, 'akismet');
        return true;
    }

    return false;
}

function sj_job_alerts_is_spam(array $args): bool {
    $context    = sanitize_key($args['context'] ?? 'job_alert');
    $voornaam   = sanitize_text_field($args['voornaam'] ?? '');
    $email      = sanitize_email($args['email'] ?? '');
    $sectors    = array_map('sanitize_title', (array) ($args['sectors'] ?? []));
    $honeypot   = trim((string) ($args['honeypot'] ?? ''));
    $started_at = absint($args['started_at'] ?? 0);

    if ($honeypot !== '') {
        sj_job_alerts_log_spam($context, 'honeypot');
        return true;
    }

    if ($started_at <= 0) {
        sj_job_alerts_log_spam($context, 'missing submit timing');
        return true;
    }

    $elapsed = time() - $started_at;
    if ($elapsed < 3 || $started_at > time() + 300) {
        sj_job_alerts_log_spam($context, 'submit timing');
        return true;
    }

    if (preg_match('/(?:https?:\/\/|www\.|<[^>]+>)/i', $voornaam)) {
        sj_job_alerts_log_spam($context, 'name contains url/html');
        return true;
    }

    $content = 'Job alert signup: ' . implode(', ', $sectors);
    if (
        function_exists('wp_check_comment_disallowed_list') &&
        wp_check_comment_disallowed_list(
            $voornaam,
            $email,
            '',
            $content,
            sj_job_alerts_get_client_ip(),
            sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'] ?? ''))
        )
    ) {
        sj_job_alerts_log_spam($context, 'comment disallowed list');
        return true;
    }

    if (sj_job_alerts_is_rate_limited($context, $email)) {
        return true;
    }

    return sj_job_alerts_akismet_flags_spam($context, $voornaam, $email, $sectors);
}

/**
 * Shortcode: [job-alerts]
 */
add_shortcode('job-alerts', 'sj_job_alerts_shortcode');

function sj_job_alerts_shortcode(): string {
    global $wpdb;
    $table   = $wpdb->prefix . 'sj_job_alerts';
    $errors  = [];
    $success = false;

    if (
        $_SERVER['REQUEST_METHOD'] === 'POST' &&
        isset($_POST['sj_ja_nonce']) &&
        wp_verify_nonce($_POST['sj_ja_nonce'], 'sj_job_alerts')
    ) {
        $voornaam = sanitize_text_field($_POST['voornaam'] ?? '');
        $email    = sanitize_email($_POST['email']    ?? '');
        $sectors  = array_map('sanitize_title', (array) ($_POST['sectors'] ?? []));

        if (!$voornaam)        $errors[] = 'Please enter your first name.';
        if (!is_email($email)) $errors[] = 'Please enter a valid email address.';
        if (empty($sectors))   $errors[] = 'Please choose at least one category.';

        if (
            empty($errors) &&
            sj_job_alerts_is_spam([
                'context'    => 'job_alerts_main',
                'voornaam'   => $voornaam,
                'email'      => $email,
                'sectors'    => $sectors,
                'honeypot'   => $_POST['sj_ja_website'] ?? '',
                'started_at' => $_POST['sj_ja_started_at'] ?? 0,
            ])
        ) {
            $errors[] = 'Your signup could not be processed. Please try again later.';
        }

        if (empty($errors)) {
            $existing = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$table} WHERE email = %s",
                $email
            ));
            $is_new = !$existing;

            if ($existing) {
                $wpdb->update(
                    $table,
                    ['voornaam' => $voornaam, 'sectors' => wp_json_encode($sectors), 'active' => 1],
                    ['email'    => $email],
                    ['%s', '%s', '%d'],
                    ['%s']
                );
            } else {
                $wpdb->insert(
                    $table,
                    ['voornaam' => $voornaam, 'email' => $email, 'sectors' => wp_json_encode($sectors), 'active' => 1],
                    ['%s', '%s', '%s', '%d']
                );
            }

            // Send emails and ActiveCampaign updates only for new signups.
            if ($is_new) {
                sj_ac_subscribe_job_alert($voornaam, $email, $sectors);

                $sector_names = [];
                foreach ($sectors as $slug) {
                    $term = get_term_by('slug', $slug, 'job_sector');
                    if ($term) $sector_names[] = $term->name;
                }

                $body  = "Hi {$voornaam},\n\n";
                $body .= "You have signed up for job alerts on Sustainablejobs.com!\n\n";
                $body .= "You will receive the latest jobs every week in:\n";
                foreach ($sector_names as $name) {
                    $body .= "- {$name}\n";
                }
                $body .= "\nWant to change your preferences or unsubscribe? Email support@sustainablejobs.com.\n\n";
                $body .= "Kind regards,\nSustainablejobs.com";

                wp_mail($email, 'Your job alert has been set up!', $body);

                $admin_email = get_option('admin_email');
                $admin_body  = "New job alert signup on Sustainablejobs.com\n\n";
                $admin_body .= "Name:  {$voornaam}\n";
                $admin_body .= "Email: {$email}\n";
                $admin_body .= "Categories:\n";
                foreach ($sector_names as $name) {
                    $admin_body .= "- {$name}\n";
                }
                wp_mail($admin_email, 'New job alert signup', $admin_body);
            }

            $success = true;
        }
    }

    $sector_terms = get_terms(['taxonomy' => 'job_sector', 'hide_empty' => false, 'orderby' => 'name']);
    if (is_wp_error($sector_terms)) {
        $sector_terms = [];
    }

    ob_start();

    if ($success): ?>

    <div class="sj-ja-notice sj-ja-notice--success">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M173.66,98.34a8,8,0,0,1,0,11.32l-56,56a8,8,0,0,1-11.32,0l-24-24a8,8,0,0,1,11.32-11.32L112,148.69l50.34-50.35A8,8,0,0,1,173.66,98.34ZM232,128A104,104,0,1,1,128,24,104.11,104.11,0,0,1,232,128Zm-16,0a88,88,0,1,0-88,88A88.1,88.1,0,0,0,216,128Z"/></svg>
        <div>
            <strong>You are signed up!</strong>
            <p>You will receive a weekly overview of new jobs in your selected categories. Please also check your spam folder.</p>
        </div>
    </div>

    <?php else: ?>

    <?php if (!empty($errors)): ?>
    <div class="sj-ja-notice sj-ja-notice--error">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M236.8,188.09,149.35,36.22a24.76,24.76,0,0,0-42.7,0L19.2,188.09a23.51,23.51,0,0,0,0,23.72A24.35,24.35,0,0,0,40.55,224h174.9a24.35,24.35,0,0,0,21.33-12.19A23.51,23.51,0,0,0,236.8,188.09ZM120,104a8,8,0,0,1,16,0v40a8,8,0,0,1-16,0Zm8,88a12,12,0,1,1,12-12A12,12,0,0,1,128,192Z"/></svg>
        <div>
            <strong>Please check the following fields:</strong>
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?php echo esc_html($e); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php endif; ?>

    <div class="sj-ja">
        <div class="sj-ja__block">

            <header class="sj-ja__header">
                <h2 class="sj-ja__title">Set up job alerts</h2>
                <p class="sj-ja__subtitle">Receive the latest jobs in your field directly in your inbox every week.</p>
            </header>

            <form method="post" class="sj-ja__form" novalidate>
                <?php wp_nonce_field('sj_job_alerts', 'sj_ja_nonce'); ?>
                <?php echo sj_job_alerts_spam_fields('sj_ja'); ?>

                <div class="sj-ja__fields">

                    <div class="sj-ja__field">
                        <label class="sj-ja__label" for="sj_ja_voornaam">First name <span class="sj-ja__req">*</span></label>
                        <div class="sj-ja__input-wrap">
                            <svg class="sj-ja__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#168AAD" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            <input type="text" name="voornaam" id="sj_ja_voornaam" class="sj-ja__input"
                                   value="<?php echo esc_attr($_POST['voornaam'] ?? ''); ?>"
                                   required>
                        </div>
                    </div>

                    <div class="sj-ja__field">
                        <label class="sj-ja__label" for="sj_ja_email">Email address <span class="sj-ja__req">*</span></label>
                        <div class="sj-ja__input-wrap">
                            <svg class="sj-ja__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#168AAD" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                            <input type="email" name="email" id="sj_ja_email" class="sj-ja__input"
                                   value="<?php echo esc_attr($_POST['email'] ?? ''); ?>"
                                   required>
                        </div>
                    </div>

                    <?php
                    $selected_sectors = array_map('sanitize_title', (array) ($_POST['sectors'] ?? []));
                    ?>
                    <div class="sj-ja__field sj-ja__field--full">
                        <label class="sj-ja__label" for="sj_ja_sectors">Categories <span class="sj-ja__req">*</span></label>
                        <select name="sectors[]" id="sj_ja_sectors"
                                class="js-custom-select"
                                data-placeholder="Categories"
                                multiple>
                            <?php foreach ($sector_terms as $term): ?>
                            <option value="<?php echo esc_attr($term->slug); ?>"
                                <?php selected(in_array($term->slug, $selected_sectors)); ?>>
                                <?php echo esc_html($term->name); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                </div>

                <div class="sj-ja-active-filters" id="sj_ja_active_filters" aria-live="polite"></div>

                <footer class="sj-ja__footer">
                    <button type="submit" class="sj-ja__submit" data-label="Set up alert" data-loading="Sending...">Set up alert</button>
                </footer>

            </form>
        </div>
    </div>

    <script>
    (function () {
        var form = document.querySelector('.sj-ja__form');
        if (form) {
            form.addEventListener('submit', function () {
                var btn = form.querySelector('.sj-ja__submit');
                if (btn) {
                    btn.disabled = true;
                    btn.textContent = btn.dataset.loading || 'Sending...';
                    btn.style.opacity = '0.65';
                }
            });
        }

        var closeAll = function () {
            document.querySelectorAll('.sj-select.active').forEach(function (el) {
                el.classList.remove('active');
                var searchInput = el.querySelector('.sj-search-input');
                if (searchInput) {
                    searchInput.value = '';
                    el.querySelectorAll('.sj-option').forEach(function (o) { o.style.display = ''; });
                }
            });
        };

        var buildSelect = function (select) {
            if (select.classList.contains('sj-hidden-select')) return;

            var placeholder = select.dataset.placeholder || 'Select';

            var wrap = document.createElement('div');
            wrap.className = 'sj-select-wrap';
            select.parentNode.insertBefore(wrap, select);
            wrap.appendChild(select);

            var root = document.createElement('div');
            root.className = 'sj-select';
            root.dataset.type = 'multi';

            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'sj-select-btn';
            btn.innerHTML = '<span class="sj-btn-content"><span class="sj-placeholder"></span><span class="sj-tags" aria-hidden="true"></span></span><span class="sj-actions"><span class="sj-clear" role="button" aria-label="Clear selection" title="Clear selection">×</span><span class="sj-chev" aria-hidden="true"></span></span>';
            btn.querySelector('.sj-placeholder').textContent = placeholder;

            var clearBtn = btn.querySelector('.sj-clear');
            clearBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                e.preventDefault();
                Array.from(select.options).forEach(function (o) { o.selected = false; });
                renderState();
            });

            var list = document.createElement('div');
            list.className = 'sj-options';
            list.setAttribute('role', 'listbox');
            list.setAttribute('aria-multiselectable', 'true');

            var searchWrap = document.createElement('div');
            searchWrap.className = 'sj-search';
            searchWrap.innerHTML = '<input type="text" class="sj-search-input" placeholder="Search in ' + placeholder.toLowerCase() + '">';
            var searchInput = searchWrap.querySelector('.sj-search-input');
            list.appendChild(searchWrap);

            var optionRows = [];
            Array.from(select.options).forEach(function (opt) {
                var row = document.createElement('div');
                row.className = 'sj-option';
                row.dataset.value = opt.value;
                row.setAttribute('role', 'option');
                row.setAttribute('aria-selected', opt.selected ? 'true' : 'false');
                row.innerHTML = '<span class="sj-option-text"></span>';
                row.querySelector('.sj-option-text').textContent = opt.textContent.trim();

                var syncSelected = function () {
                    row.classList.toggle('is-selected', opt.selected);
                    row.setAttribute('aria-selected', opt.selected ? 'true' : 'false');
                };
                syncSelected();

                row.addEventListener('click', function (e) {
                    e.preventDefault();
                    if (opt.disabled) return;
                    opt.selected = !opt.selected;
                    renderState();
                });

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

            var placeholderEl = btn.querySelector('.sj-placeholder');

            var renderChips = function () {
                var form = select.closest('form');
                var container = form ? form.querySelector('.sj-ja-active-filters') : document.getElementById('sj_ja_active_filters');
                if (!container) return;
                container.innerHTML = '';
                var selected = Array.from(select.options).filter(function (o) { return o.selected; });
                container.classList.toggle('has-items', selected.length > 0);
                selected.forEach(function (opt) {
                    var chip = document.createElement('span');
                    chip.className = 'sj-ja-chip';
                    var text = document.createElement('span');
                    text.textContent = opt.textContent.trim();
                    var rm = document.createElement('button');
                    rm.type = 'button';
                    rm.className = 'sj-ja-chip__remove';
                    rm.setAttribute('aria-label', 'Remove ' + opt.textContent.trim());
                    rm.textContent = '×';
                    rm.addEventListener('click', function (e) {
                        e.preventDefault();
                        opt.selected = false;
                        renderState();
                    });
                    chip.appendChild(text);
                    chip.appendChild(rm);
                    container.appendChild(chip);
                });
            };

            var renderState = function () {
                optionRows.forEach(function (item) { item.syncSelected(); });
                var selected = Array.from(select.options).filter(function (o) { return o.selected; });
                if (selected.length === 0) {
                    placeholderEl.textContent = placeholder;
                    placeholderEl.style.display = 'inline';
                    clearBtn.style.display = 'none';
                } else if (selected.length === 1) {
                    placeholderEl.textContent = selected[0].textContent.trim();
                    placeholderEl.style.display = 'inline';
                    clearBtn.style.display = 'inline-flex';
                } else {
                    placeholderEl.textContent = selected.length + ' selected';
                    placeholderEl.style.display = 'inline';
                    clearBtn.style.display = 'inline-flex';
                }
                renderChips();
            };

            renderState();

            btn.addEventListener('click', function (e) {
                if (e.target.closest('.sj-clear')) return;
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
            select.classList.add('sj-hidden-select');
        };

        var init = function () {
            document.querySelectorAll('.sj-ja__form .js-custom-select').forEach(buildSelect);

            document.addEventListener('click', function (e) {
                if (!e.target.closest('.sj-select')) closeAll();
            });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') closeAll();
            });
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

/**
 * Shortcode: [sj-job-alerts-sidebar]
 * Compact sidebar version for the single job page.
 */
add_shortcode('sj-job-alerts-sidebar', 'sj_job_alerts_sidebar_shortcode');

function sj_job_alerts_sidebar_shortcode(): string {
    global $wpdb;
    $table   = $wpdb->prefix . 'sj_job_alerts';
    $errors  = [];
    $success = false;

    if (
        $_SERVER['REQUEST_METHOD'] === 'POST' &&
        isset($_POST['sj_ja_sb_nonce']) &&
        wp_verify_nonce($_POST['sj_ja_sb_nonce'], 'sj_job_alerts_sidebar')
    ) {
        $voornaam = sanitize_text_field($_POST['sj_ja_sb_voornaam'] ?? '');
        $email    = sanitize_email($_POST['sj_ja_sb_email']         ?? '');
        $sectors  = array_map('sanitize_title', (array) ($_POST['sj_ja_sb_sectors'] ?? []));

        if (!$voornaam)        $errors[] = 'Please enter your first name.';
        if (!is_email($email)) $errors[] = 'Please enter a valid email address.';
        if (empty($sectors))   $errors[] = 'Please choose at least one category.';

        if (
            empty($errors) &&
            sj_job_alerts_is_spam([
                'context'    => 'job_alerts_sidebar',
                'voornaam'   => $voornaam,
                'email'      => $email,
                'sectors'    => $sectors,
                'honeypot'   => $_POST['sj_ja_sb_website'] ?? '',
                'started_at' => $_POST['sj_ja_sb_started_at'] ?? 0,
            ])
        ) {
            $errors[] = 'Your signup could not be processed. Please try again later.';
        }

        if (empty($errors)) {
            $existing = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$table} WHERE email = %s", $email
            ));
            $is_new = !$existing;
            $saved  = false;

            if ($existing) {
                $updated = $wpdb->update(
                    $table,
                    ['voornaam' => $voornaam, 'sectors' => wp_json_encode($sectors), 'active' => 1],
                    ['email'    => $email],
                    ['%s', '%s', '%d'],
                    ['%s']
                );
                $saved = ($updated !== false);
            } else {
                $inserted = $wpdb->insert(
                    $table,
                    ['voornaam' => $voornaam, 'email' => $email, 'sectors' => wp_json_encode($sectors), 'active' => 1],
                    ['%s', '%s', '%s', '%d']
                );
                $saved = ($inserted !== false);
            }

            if (!$saved) {
                error_log('[SJ Job Alerts Sidebar] Signup save failed: ' . $wpdb->last_error);
                $errors[] = 'Your job alert could not be saved. Please try again later.';
            } else {
                // Send emails and ActiveCampaign updates only for new signups.
                if ($is_new) {
                    sj_ac_subscribe_job_alert($voornaam, $email, $sectors);

                    $sector_names = [];
                    foreach ($sectors as $slug) {
                        $term = get_term_by('slug', $slug, 'job_sector');
                        if ($term) $sector_names[] = $term->name;
                    }

                    $body  = "Hi {$voornaam},\n\n";
                    $body .= "You have signed up for job alerts on Sustainablejobs.com!\n\n";
                    $body .= "You will receive the latest jobs every week in:\n";
                    foreach ($sector_names as $name) {
                        $body .= "- {$name}\n";
                    }
                    $body .= "\nWant to change your preferences or unsubscribe? Email support@sustainablejobs.com.\n\nKind regards,\nSustainablejobs.com";

                    wp_mail($email, 'Your job alert has been set up!', $body);
                }

                $success = true;
            }
        }
    }

    $sector_terms = get_terms(['taxonomy' => 'job_sector', 'hide_empty' => false, 'orderby' => 'name']);
    if (is_wp_error($sector_terms)) $sector_terms = [];

    ob_start();

    if ($success): ?>

    <div class="sj-ja-sb__success">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M173.66,98.34a8,8,0,0,1,0,11.32l-56,56a8,8,0,0,1-11.32,0l-24-24a8,8,0,0,1,11.32-11.32L112,148.69l50.34-50.35A8,8,0,0,1,173.66,98.34ZM232,128A104,104,0,1,1,128,24,104.11,104.11,0,0,1,232,128Zm-16,0a88,88,0,1,0-88,88A88.1,88.1,0,0,0,216,128Z"/></svg>
        <span>Your alert has been set up! Please also check your spam folder.</span>
    </div>

    <?php else: ?>

    <p class="sj-ja-sb__subtitle">Receive new jobs in your field every week.</p>

    <?php if (!empty($errors)): ?>
    <div class="sj-ja-sb__errors">
        <?php foreach ($errors as $e): ?>
            <p><?php echo esc_html($e); ?></p>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <form method="post" class="sj-ja-sb__form" novalidate>
        <?php wp_nonce_field('sj_job_alerts_sidebar', 'sj_ja_sb_nonce'); ?>
        <?php echo sj_job_alerts_spam_fields('sj_ja_sb'); ?>

        <div class="sj-ja-sb__field">
            <label class="sj-ja-sb__label" for="sj_ja_sb_voornaam">First name <span class="sj-ja-sb__req">*</span></label>
            <input type="text" name="sj_ja_sb_voornaam" id="sj_ja_sb_voornaam" class="sj-ja-sb__input"
                   value="<?php echo esc_attr($_POST['sj_ja_sb_voornaam'] ?? ''); ?>" required>
        </div>

        <div class="sj-ja-sb__field">
            <label class="sj-ja-sb__label" for="sj_ja_sb_email">Email address <span class="sj-ja-sb__req">*</span></label>
            <input type="email" name="sj_ja_sb_email" id="sj_ja_sb_email" class="sj-ja-sb__input"
                   value="<?php echo esc_attr($_POST['sj_ja_sb_email'] ?? ''); ?>" required>
        </div>

        <div class="sj-ja-sb__field">
            <label class="sj-ja-sb__label" for="sj_ja_sb_sectors">Categories <span class="sj-ja-sb__req">*</span></label>
            <?php $selected_sectors = array_map('sanitize_title', (array) ($_POST['sj_ja_sb_sectors'] ?? [])); ?>
            <select name="sj_ja_sb_sectors[]" id="sj_ja_sb_sectors" class="js-custom-select" data-placeholder="Categories" multiple>
                <?php foreach ($sector_terms as $term): ?>
                <option value="<?php echo esc_attr($term->slug); ?>"
                    <?php selected(in_array($term->slug, $selected_sectors)); ?>>
                    <?php echo esc_html($term->name); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="sj-ja-sb__submit" data-label="Set up alert" data-loading="Sending...">Set up alert</button>
    </form>

    <script>
    (function () {
        var sbForm = document.querySelector('.sj-ja-sb__form');
        if (sbForm) {
            sbForm.addEventListener('submit', function () {
                var btn = sbForm.querySelector('.sj-ja-sb__submit');
                if (btn) {
                    btn.disabled = true;
                    btn.textContent = btn.dataset.loading || 'Sending...';
                    btn.style.opacity = '0.65';
                }
            });
        }

        var closeAll = function () {
            document.querySelectorAll('.sj-select.active').forEach(function (el) {
                el.classList.remove('active');
                var s = el.querySelector('.sj-search-input');
                if (s) { s.value = ''; el.querySelectorAll('.sj-option').forEach(function (o) { o.style.display = ''; }); }
            });
        };

        var buildSelect = function (select) {
            if (select.classList.contains('sj-hidden-select')) return;
            var placeholder = select.dataset.placeholder || 'Select';

            var wrap = document.createElement('div');
            wrap.className = 'sj-select-wrap';
            select.parentNode.insertBefore(wrap, select);
            wrap.appendChild(select);

            var root = document.createElement('div');
            root.className = 'sj-select';

            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'sj-select-btn';
            btn.innerHTML = '<span class="sj-btn-content"><span class="sj-placeholder"></span></span><span class="sj-actions"><span class="sj-clear" role="button" aria-label="Clear selection" title="Clear selection">×</span><span class="sj-chev" aria-hidden="true"></span></span>';
            btn.querySelector('.sj-placeholder').textContent = placeholder;

            var clearBtn = btn.querySelector('.sj-clear');
            clearBtn.addEventListener('click', function (e) {
                e.stopPropagation(); e.preventDefault();
                Array.from(select.options).forEach(function (o) { o.selected = false; });
                renderState();
            });

            var list = document.createElement('div');
            list.className = 'sj-options';
            list.setAttribute('role', 'listbox');
            list.setAttribute('aria-multiselectable', 'true');

            var searchWrap = document.createElement('div');
            searchWrap.className = 'sj-search';
            searchWrap.innerHTML = '<input type="text" class="sj-search-input" placeholder="Search in ' + placeholder.toLowerCase() + '">';
            var searchInput = searchWrap.querySelector('.sj-search-input');
            list.appendChild(searchWrap);

            var optionRows = [];
            Array.from(select.options).forEach(function (opt) {
                var row = document.createElement('div');
                row.className = 'sj-option';
                row.dataset.value = opt.value;
                row.setAttribute('role', 'option');
                row.innerHTML = '<span class="sj-option-text"></span>';
                row.querySelector('.sj-option-text').textContent = opt.textContent.trim();

                var syncSelected = function () {
                    row.classList.toggle('is-selected', opt.selected);
                    row.setAttribute('aria-selected', opt.selected ? 'true' : 'false');
                };
                syncSelected();
                row.addEventListener('click', function (e) {
                    e.preventDefault();
                    if (opt.disabled) return;
                    opt.selected = !opt.selected;
                    renderState();
                });
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

            var renderState = function () {
                optionRows.forEach(function (item) { item.syncSelected(); });
                var selected = Array.from(select.options).filter(function (o) { return o.selected; });
                clearBtn.style.display = selected.length ? 'inline-flex' : 'none';
                var ph = btn.querySelector('.sj-placeholder');
                if (selected.length === 0) {
                    ph.textContent = placeholder;
                } else if (selected.length === 1) {
                    ph.textContent = selected[0].textContent.trim();
                } else {
                    ph.textContent = selected.length + ' selected';
                }
            };

            renderState();

            btn.addEventListener('click', function (e) {
                if (e.target.closest('.sj-clear')) return;
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
            select.classList.add('sj-hidden-select');
        };

        var init = function () {
            document.querySelectorAll('.sj-ja-sb__form .js-custom-select').forEach(buildSelect);
            document.addEventListener('click', function (e) { if (!e.target.closest('.sj-select')) closeAll(); });
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
