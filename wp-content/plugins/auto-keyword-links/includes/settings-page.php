<?php
if (!defined('ABSPATH')) exit;

add_action('admin_menu', function () {
    add_options_page(
        'Keyword Links',
        'Keyword Links',
        'manage_options',
        'akl-keyword-links',
        'akl_render_settings_page'
    );
});

function akl_render_settings_page() {
    if (!current_user_can('manage_options')) return;

    $links = get_option('akl_keyword_links', []);
    $page_url = admin_url('options-general.php?page=akl-keyword-links');

    // Nieuwe koppeling toevoegen
    if (isset($_POST['akl_action']) && $_POST['akl_action'] === 'add') {
        check_admin_referer('akl_add_link');

        $keyword = trim(wp_unslash($_POST['akl_keyword'] ?? ''));
        $url     = trim(wp_unslash($_POST['akl_url'] ?? ''));

        if ($keyword !== '' && $url !== '') {
            $links[] = [
                'keyword' => sanitize_text_field($keyword),
                'url'     => esc_url_raw($url),
            ];
            update_option('akl_keyword_links', $links);
            echo '<div class="notice notice-success"><p>Koppeling toegevoegd.</p></div>';
        }
    }

    // Koppeling verwijderen
    if (isset($_GET['akl_delete'])) {
        check_admin_referer('akl_delete_link');

        $index = (int) $_GET['akl_delete'];
        if (isset($links[$index])) {
            unset($links[$index]);
            $links = array_values($links);
            update_option('akl_keyword_links', $links);
            echo '<div class="notice notice-success"><p>Koppeling verwijderd.</p></div>';
        }
    }
    ?>
    <div class="wrap">
        <h1>Keyword Links</h1>
        <p>Het eerste voorkomen van elk keyword hieronder wordt automatisch gelinkt naar de bijbehorende URL &mdash; &eacute;&eacute;n keer per pagina, niet hoofdlettergevoelig, en er wordt niet gelinkt binnen bestaande links of op de doelpagina zelf.</p>

        <table class="widefat striped" style="max-width:800px;">
            <thead>
                <tr>
                    <th>Keyword</th>
                    <th>URL</th>
                    <th style="width:100px;"></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($links)) : ?>
                    <tr><td colspan="3">Nog geen koppelingen toegevoegd.</td></tr>
                <?php endif; ?>
                <?php foreach ($links as $i => $link) : ?>
                    <tr>
                        <td><?php echo esc_html($link['keyword']); ?></td>
                        <td><a href="<?php echo esc_url($link['url']); ?>" target="_blank" rel="noopener"><?php echo esc_html($link['url']); ?></a></td>
                        <td>
                            <a href="<?php echo esc_url(wp_nonce_url(add_query_arg('akl_delete', $i, $page_url), 'akl_delete_link')); ?>"
                               onclick="return confirm('Weet je zeker dat je deze koppeling wilt verwijderen?');">
                                Verwijderen
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h2>Nieuwe koppeling toevoegen</h2>
        <form method="post" action="<?php echo esc_url($page_url); ?>">
            <?php wp_nonce_field('akl_add_link'); ?>
            <input type="hidden" name="akl_action" value="add">
            <table class="form-table">
                <tr>
                    <th><label for="akl_keyword">Keyword</label></th>
                    <td><input type="text" id="akl_keyword" name="akl_keyword" class="regular-text" required></td>
                </tr>
                <tr>
                    <th><label for="akl_url">URL</label></th>
                    <td><input type="url" id="akl_url" name="akl_url" class="regular-text" required placeholder="https://..."></td>
                </tr>
            </table>
            <?php submit_button('Koppeling toevoegen'); ?>
        </form>
    </div>
    <?php
}
