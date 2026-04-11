<?php if (!defined('ABSPATH')) exit; ?>

<?php
$search         = isset($search) ? (string) $search : '';
$types_selected = isset($types_selected) && is_array($types_selected) ? $types_selected : [];
$active_view    = isset($active_view) && in_array($active_view, ['new', 'used'], true) ? $active_view : 'new';

$types = get_terms([
    'taxonomy'   => 'mh_unit_type',
    'hide_empty' => false,
]);
?>

<div class="mh-units-filter-wrap mh-units-filter-wrap--theme">
    <div class="mh-units-filter-header">
        <h2 class="mh-units-filter-title">Doorzoek alle modulaire units</h2>
        <p class="mh-units-filter-copy">
            Of <a href="<?php echo esc_url(home_url('/offerte-aanvragen/')); ?>" class="mh-units-filter-link">plaats jouw units</a>
            in het netwerk van ModulaireHuisvesting.nl
        </p>
    </div>

    <form class="mh-units-filter-form" method="get">
        <div class="mh-units-view-toggle" role="radiogroup" aria-label="Selecteer type aanbod">
            <label class="mh-units-view-toggle__option<?php echo 'new' === $active_view ? ' is-active' : ''; ?>">
                <input
                    class="mh-units-view-toggle__input"
                    type="radio"
                    name="mh_units_state"
                    value="new"
                    <?php checked('new', $active_view); ?>
                >
                <span>Nieuwe units</span>
            </label>

            <label class="mh-units-view-toggle__option<?php echo 'used' === $active_view ? ' is-active' : ''; ?>">
                <input
                    class="mh-units-view-toggle__input"
                    type="radio"
                    name="mh_units_state"
                    value="used"
                    <?php checked('used', $active_view); ?>
                >
                <span>Gebruikte units</span>
            </label>
        </div>

        <div class="mh-units-filter-controls">
            <div class="mh-units-filter-field mh-units-filter-field--search">
                <label for="mh_search">Zoek</label>
                <input
                    id="mh_search"
                    name="mh_search"
                    type="text"
                    placeholder="Zoek op naam, trefwoord..."
                    value="<?php echo esc_attr($search); ?>"
                >
            </div>

            <?php if (!is_wp_error($types) && !empty($types)): ?>
                <fieldset class="mh-units-filter-field mh-units-filter-field--types">
                    <legend>Type unit</legend>

                    <div class="mh-units-filter-pills">
                        <?php foreach ($types as $type): ?>
                            <label class="mh-units-filter-pill">
                                <input
                                    type="checkbox"
                                    name="mh_type[]"
                                    value="<?php echo esc_attr($type->slug); ?>"
                                    <?php checked(in_array($type->slug, $types_selected, true)); ?>
                                >
                                <span><?php echo esc_html($type->name); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </fieldset>
            <?php endif; ?>
        </div>

        <div class="mh-units-filter-actions">
            <button type="submit" class="mh-units-filter-submit">Filters toepassen</button>
            <a class="mh-units-filter-reset" href="<?php echo esc_url(get_permalink()); ?>">Reset</a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var form = document.querySelector('.mh-units-filter-form');
  if (!form) return;

  form.querySelectorAll('.mh-units-view-toggle__input').forEach(function (input) {
    input.addEventListener('change', function () {
      form.submit();
    });
  });
});
</script>
