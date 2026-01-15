<?php
if ( ! defined('ABSPATH') ) exit;

$search_query    = $context['search_query'];
$types           = $context['types'];
$sectors         = $context['sectors'];
$selected_types  = $context['selected_types'];
$selected_sector = $context['selected_sector'];
?>

<style>
/* ===== Fondsen.org-achtige filter UI (basis) ===== */
.org-filter {
    width: 1050px;
    max-width: calc(100% - 24px);
    margin: 0 auto 28px;
    padding: 24px;
    border: 1px solid #E1E1E1;
    border-radius: 10px;
    background: #fff;
    box-shadow: 0 10px 40px -5px rgba(0,0,0,.12);
    font-family: Poppins, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
}

.org-filter__row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
    margin-top: 16px;
}

.org-filter__top label,
.org-filter__item label {
    display: block;
    font-weight: 600;
    font-size: 14px;
    color: #333;
    margin-bottom: 8px;
}

.org-filter__top input[type="text"] {
    width: 100%;
    min-height: 46px;
    border: 1px solid #d0d5dd;
    border-radius: 10px;
    padding: 12px 14px 12px 42px;
    font-size: 15px;
    outline: none;
    background-color: #fff;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512' fill='%23999999'%3E%3Cpath d='M505 442.7L405.3 343c28.4-34.9 45.7-79.4 45.7-128C451 96.5 354.5 0 233.5 0S16 96.5 16 215.5 112.5 431 233.5 431c48.6 0 93.1-17.3 128-45.7L461.3 505c9.4 9.4 24.6 9.4 33.9 0l9.8-9.8c9.4-9.4 9.4-24.6 0-33.9zM233.5 383c-92.6 0-167.5-74.9-167.5-167.5S140.9 48 233.5 48 401 122.9 401 215.5 326.1 383 233.5 383z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: 14px center;
    background-size: 16px;
}

.org-filter__top input[type="text"]:focus {
    border-color: #0884CC;
    box-shadow: 0 0 0 3px rgba(8,132,204,0.12);
}

/* ===== Custom multiselect (Fondsen-stijl) ===== */
.ms {
    position: relative;
}

.ms__control {
    min-height: 46px;
    border: 1px solid #d0d5dd;
    border-radius: 10px;
    background: #fff;
    padding: 8px 10px;
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    cursor: pointer;
}

.ms__control:focus-within,
.ms.is-open .ms__control {
    border-color: #0884CC;
    box-shadow: 0 0 0 3px rgba(8,132,204,0.12);
}

.ms__placeholder {
    color: #667085;
    font-size: 15px;
    user-select: none;
}

.ms__chips {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.ms__chip {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 10px;
    border-radius: 999px;
    background: #F2F4F7;
    border: 1px solid #E4E7EC;
    font-size: 13px;
    color: #344054;
}

.ms__chip button {
    border: 0;
    background: transparent;
    cursor: pointer;
    font-size: 14px;
    line-height: 1;
    color: #667085;
}

.ms__spacer { flex: 1; }

.ms__caret {
    width: 10px;
    height: 10px;
    border-right: 2px solid #667085;
    border-bottom: 2px solid #667085;
    transform: rotate(45deg);
    margin-right: 6px;
}

.ms.is-open .ms__caret {
    transform: rotate(-135deg);
    margin-top: 4px;
}

.ms__panel {
    position: absolute;
    top: calc(100% + 8px);
    left: 0;
    right: 0;
    background: #fff;
    border: 1px solid #E4E7EC;
    border-radius: 12px;
    box-shadow: 0 18px 60px rgba(16,24,40,0.18);
    padding: 12px;
    z-index: 9999;
}

.ms__panel[hidden] { display: none; }

.ms__panel-top {
    display: flex;
    gap: 10px;
    align-items: center;
    margin-bottom: 10px;
}

.ms__search {
    width: 100%;
    min-height: 40px;
    border: 1px solid #E4E7EC;
    border-radius: 10px;
    padding: 10px 12px;
    font-size: 14px;
    outline: none;
}

.ms__search:focus {
    border-color: #0884CC;
    box-shadow: 0 0 0 3px rgba(8,132,204,0.10);
}

.ms__clear {
    border: 1px solid #E4E7EC;
    background: #fff;
    border-radius: 10px;
    padding: 10px 12px;
    font-size: 13px;
    cursor: pointer;
    white-space: nowrap;
}

.ms__options {
    max-height: 260px;
    overflow: auto;
    padding-right: 4px;
}

.ms__option {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 10px;
    border-radius: 10px;
    cursor: pointer;
}

.ms__option:hover {
    background: #F9FAFB;
}

.ms__option input {
    width: 16px;
    height: 16px;
}

.ms__option span {
    font-size: 14px;
    color: #344054;
}

@media (max-width: 900px) {
    .org-filter__row {
        grid-template-columns: 1fr;
    }
}
</style>

<form method="get" id="orgFilterForm" class="org-filter">
    <div class="org-filter__top">
        <label for="org_search">Zoek organisatie</label>
        <input
            type="text"
            id="org_search"
            name="org_search"
            value="<?php echo esc_attr($search_query); ?>"
            placeholder="Naam, onderwerp, trefwoord..."
            autocomplete="off"
        >
    </div>

    <div class="org-filter__row">
        <!-- Type organisatie -->
        <div class="org-filter__item">
            <label>Type organisatie</label>

            <div class="ms js-ms" data-name="org_type" data-placeholder="Kies type(s)">
                <div class="ms__control js-ms-control" tabindex="0" role="button" aria-expanded="false">
                    <div class="ms__chips js-ms-chips"></div>
                    <div class="ms__placeholder js-ms-placeholder">Kies type(s)</div>
                    <div class="ms__spacer"></div>
                    <div class="ms__caret" aria-hidden="true"></div>
                </div>

                <div class="ms__panel js-ms-panel" hidden>
                    <div class="ms__panel-top">
                        <input type="text" class="ms__search js-ms-search" placeholder="Zoek..." autocomplete="off">
                        <button type="button" class="ms__clear js-ms-clear">Wissen</button>
                    </div>

                    <div class="ms__options js-ms-options">
                        <?php if ( ! empty($types) && ! is_wp_error($types) ) : ?>
                            <?php foreach ($types as $term) : ?>
                                <?php $checked = in_array($term->slug, $selected_types, true); ?>
                                <label class="ms__option" data-label="<?php echo esc_attr( strtolower($term->name) ); ?>">
                                    <input type="checkbox" value="<?php echo esc_attr($term->slug); ?>" <?php checked($checked); ?>>
                                    <span><?php echo esc_html($term->name); ?></span>
                                </label>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div style="padding:10px;color:#667085;font-size:14px;">Geen opties</div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php foreach ($selected_types as $slug) : ?>
                    <input type="hidden" name="org_type[]" value="<?php echo esc_attr($slug); ?>">
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Sector -->
        <div class="org-filter__item">
            <label>Sector</label>

            <div class="ms js-ms" data-name="org_sector" data-placeholder="Kies sector(en)">
                <div class="ms__control js-ms-control" tabindex="0" role="button" aria-expanded="false">
                    <div class="ms__chips js-ms-chips"></div>
                    <div class="ms__placeholder js-ms-placeholder">Kies sector(en)</div>
                    <div class="ms__spacer"></div>
                    <div class="ms__caret" aria-hidden="true"></div>
                </div>

                <div class="ms__panel js-ms-panel" hidden>
                    <div class="ms__panel-top">
                        <input type="text" class="ms__search js-ms-search" placeholder="Zoek..." autocomplete="off">
                        <button type="button" class="ms__clear js-ms-clear">Wissen</button>
                    </div>

                    <div class="ms__options js-ms-options">
                        <?php if ( ! empty($sectors) && ! is_wp_error($sectors) ) : ?>
                            <?php foreach ($sectors as $term) : ?>
                                <?php $checked = in_array($term->slug, $selected_sector, true); ?>
                                <label class="ms__option" data-label="<?php echo esc_attr( strtolower($term->name) ); ?>">
                                    <input type="checkbox" value="<?php echo esc_attr($term->slug); ?>" <?php checked($checked); ?>>
                                    <span><?php echo esc_html($term->name); ?></span>
                                </label>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div style="padding:10px;color:#667085;font-size:14px;">Geen opties</div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php foreach ($selected_sector as $slug) : ?>
                    <input type="hidden" name="org_sector[]" value="<?php echo esc_attr($slug); ?>">
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <noscript style="margin-top:12px;display:block;">
        <button type="submit">Filteren</button>
    </noscript>
</form>
